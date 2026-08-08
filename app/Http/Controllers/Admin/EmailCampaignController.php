<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CampagneMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Envoi d'e-mails en masse aux utilisateurs.
 *
 * L'hébergement mutualisé n'a pas de worker de file permanent : l'envoi se
 * fait donc par petits lots, déclenchés en boucle par la page d'administration
 * (fetch), avec une barre de progression. Rien ne bloque, l'admin voit
 * l'avancement, et une campagne interrompue peut reprendre là où elle s'est
 * arrêtée (chaque destinataire est marqué en base).
 */
class EmailCampaignController extends Controller
{
    /** Nombre d'e-mails envoyés par appel : compromis vitesse / timeout. */
    private const TAILLE_LOT = 15;

    public function index(): Response
    {
        return Inertia::render('admin/emails/index', [
            'campagnes' => EmailCampaign::orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(fn (EmailCampaign $c) => $this->formatCampagne($c)),
            'nb_destinataires' => User::whereNotNull('email')->where('email', '<>', '')->count(),
        ]);
    }

    /** Crée la campagne et la liste de ses destinataires (une seule fois). */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sujet' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string', 'max:20000'],
        ]);

        $campagne = EmailCampaign::create([
            'sujet' => $validated['sujet'],
            'contenu' => $validated['contenu'],
            'cible' => 'avec_email',
            'statut' => 'en_cours',
            'id_admin' => Auth::id(),
        ]);

        // Destinataires figés au lancement : insertion en masse, dédupliquée.
        $now = now();
        User::whereNotNull('email')->where('email', '<>', '')
            ->select('id', 'email')
            ->distinct()
            ->chunkById(500, function ($users) use ($campagne, $now) {
                $lignes = $users->map(fn ($u) => [
                    'campaign_id' => $campagne->id,
                    'user_id' => $u->id,
                    'email' => $u->email,
                    'statut' => 'en_attente',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                // insertOrIgnore : la contrainte unique (campagne,email) évite
                // les doublons si deux comptes partagent une adresse.
                EmailCampaignRecipient::insertOrIgnore($lignes);
            });

        $campagne->update(['total' => $campagne->recipients()->count()]);

        return response()->json([
            'ok' => true,
            'campagne' => $this->formatCampagne($campagne->fresh()),
        ], 201);
    }

    /** Envoie le lot suivant. Appelée en boucle par la page tant qu'il reste. */
    public function envoyerLot(EmailCampaign $campagne): JsonResponse
    {
        $lot = $campagne->recipients()
            ->where('statut', 'en_attente')
            ->limit(self::TAILLE_LOT)
            ->get();

        foreach ($lot as $dest) {
            try {
                Mail::to($dest->email)->send(new CampagneMail($campagne));
                $dest->update(['statut' => 'envoye', 'envoye_at' => now()]);
            } catch (\Throwable $e) {
                $dest->update(['statut' => 'echec']);
                Log::error("Campagne {$campagne->id} — échec {$dest->email} : ".$e->getMessage());
            }
        }

        // Compteurs recalculés depuis la source de vérité.
        $envoyes = $campagne->recipients()->where('statut', 'envoye')->count();
        $echecs = $campagne->recipients()->where('statut', 'echec')->count();
        $restant = $campagne->recipients()->where('statut', 'en_attente')->count();

        $campagne->update([
            'envoyes' => $envoyes,
            'echecs' => $echecs,
            'statut' => $restant === 0 ? 'termine' : 'en_cours',
            'termine_at' => $restant === 0 ? now() : null,
        ]);

        return response()->json([
            'ok' => true,
            'termine' => $restant === 0,
            'campagne' => $this->formatCampagne($campagne->fresh()),
        ]);
    }

    public function destroy(EmailCampaign $campagne): RedirectResponse
    {
        $campagne->delete(); // les destinataires suivent (cascade)

        return back()->with('success', 'Campagne supprimée.');
    }

    private function formatCampagne(EmailCampaign $c): array
    {
        return [
            'id' => $c->id,
            'sujet' => $c->sujet,
            'total' => $c->total,
            'envoyes' => $c->envoyes,
            'echecs' => $c->echecs,
            'restant' => max(0, $c->total - $c->envoyes - $c->echecs),
            'statut' => $c->statut,
            'progression' => $c->total > 0 ? (int) round(($c->envoyes + $c->echecs) / $c->total * 100) : 0,
            'date' => $c->created_at?->isoFormat('D MMM YYYY [à] HH:mm'),
        ];
    }
}
