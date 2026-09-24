<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedIp;
use App\Models\FestyRegistration;
use App\Models\FestySetting;
use App\Models\FestyTeam;
use App\Models\FestyTicket;
use App\Models\User;
use App\Services\FestyTickets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administration de PARADISIA FESTY : réglages, équipes (dont le lien du
 * groupe WhatsApp) et liste des inscrits.
 */
class FestyController extends Controller
{
    public function index(): Response
    {
        $settings = FestySetting::actuel();

        $equipes = FestyTeam::orderBy('position')
            ->withCount('registrations')
            ->get()
            ->map(fn (FestyTeam $t) => [
                'id' => $t->id,
                'nom' => $t->nom,
                'trait' => $t->trait,
                'couleur' => $t->couleur,
                'emoji' => $t->emoji,
                'whatsapp_group' => $t->whatsapp_group,
                'actif' => $t->actif,
                'membres' => $t->registrations_count,
            ]);

        return Inertia::render('admin/festy/index', [
            'settings' => $settings->only([
                'titre', 'sous_titre', 'date_label', 'prix', 'description', 'inscriptions_ouvertes',
                'prix_participant', 'prix_fan', 'prix_participant_promo', 'prix_fan_promo', 'promo_fin',
                'places_participant_equipe',
            ]),
            'equipes' => $equipes,
            'stats' => [
                'inscrits' => FestyRegistration::count(),
                'equipes' => FestyTeam::where('actif', true)->count(),
                'sans_groupe' => FestyTeam::where('actif', true)->whereNull('whatsapp_group')->count(),
            ],
        ]);
    }

    /** Enregistre les réglages généraux. */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:120'],
            'sous_titre' => ['nullable', 'string', 'max:255'],
            'date_label' => ['nullable', 'string', 'max:120'],
            'prix' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'inscriptions_ouvertes' => ['boolean'],
            'prix_participant' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'prix_fan' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'prix_participant_promo' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'prix_fan_promo' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'promo_fin' => ['nullable', 'date'],
            'places_participant_equipe' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        FestySetting::actuel()->update($validated);

        return back()->with('success', 'Réglages enregistrés.');
    }

    /** Liste des tickets vendus, filtrable par statut. */
    public function tickets(Request $request, FestyTickets $service): Response
    {
        $statut = $request->string('statut')->toString() ?: null;

        $query = FestyTicket::with(['user', 'team'])->latest();
        if ($statut) {
            $query->where('statut', $statut);
        }

        $tickets = $query->limit(300)->get()->map(fn (FestyTicket $t) => [
            'id' => $t->id,
            'reference' => $t->reference,
            'code' => $t->code_ticket,
            'type' => $t->type,
            'type_libelle' => $t->typeLibelle(),
            'montant' => $t->montant,
            'promo' => $t->promo,
            'moyen' => $t->moyen,
            'statut' => $t->statut,
            'client' => $t->user?->name,
            'email' => $t->user?->email,
            'telephone' => $t->telephone ?: $t->user?->phone,
            'equipe' => $t->team?->nom,
            'couleur' => $t->team?->couleur,
            'date' => $t->created_at->isoFormat('D MMM YYYY [à] HH:mm'),
            'paye_le' => $t->paid_at?->isoFormat('D MMM YYYY [à] HH:mm'),
        ]);

        $settings = FestySetting::actuel();

        return Inertia::render('admin/festy/tickets', [
            'tickets' => $tickets,
            'filtre' => $statut,
            'equipes' => FestyTeam::where('actif', true)->orderBy('position')->get(['id', 'nom', 'couleur'])
                ->map(function (FestyTeam $t) use ($service) {
                    $p = $service->placesParticipant($t->id);

                    return [
                        'id' => $t->id, 'nom' => $t->nom, 'couleur' => $t->couleur,
                        'places_restantes' => $p['restantes'], 'occupees' => $p['occupees'],
                        'limite' => $p['limite'], 'complet' => $p['complet'],
                    ];
                }),
            'prix' => [
                'participant' => $settings->prixTicket('participant'),
                'fan' => $settings->prixTicket('fan'),
            ],
            'stats' => [
                'total' => FestyTicket::count(),
                'payes' => FestyTicket::where('statut', 'paye')->count(),
                'en_attente' => FestyTicket::where('statut', 'en_attente')->count(),
                'recette' => (int) FestyTicket::where('statut', 'paye')->sum('montant'),
                'participants' => FestyTicket::where('statut', 'paye')->where('type', 'participant')->count(),
                'fans' => FestyTicket::where('statut', 'paye')->where('type', 'fan')->count(),
            ],
        ]);
    }

    /** Valide un ticket Orange Money (preuve reçue) : envoi e-mail + notif. */
    public function validerTicket(FestyTicket $ticket, FestyTickets $service): RedirectResponse
    {
        if ($ticket->statut === 'paye') {
            return back()->with('info', 'Ce ticket est déjà validé.');
        }

        $envoye = $service->finaliser($ticket, auth()->id());

        return $envoye
            ? back()->with('success', "Ticket {$ticket->code_ticket} validé — envoyé par e-mail au client.")
            : back()->with('error', "Ticket {$ticket->code_ticket} validé, mais l'e-mail n'a pas pu partir. Configurez les Réglages e-mail puis cliquez « Renvoyer l'e-mail ».");
    }

    /** Renvoie le ticket par e-mail (échec précédent, e-mail perdu…). */
    public function renvoyerTicket(FestyTicket $ticket, FestyTickets $service): RedirectResponse
    {
        if ($ticket->statut !== 'paye') {
            return back()->with('error', 'Seul un ticket validé peut être renvoyé.');
        }

        return $service->renvoyer($ticket)
            ? back()->with('success', "Ticket {$ticket->code_ticket} renvoyé par e-mail.")
            : back()->with('error', "L'e-mail n'a pas pu être envoyé. Vérifiez les Réglages e-mail (SMTP).");
    }

    /** Annule un ticket, quel que soit son statut (erreur, remboursement, doublon). */
    public function refuserTicket(FestyTicket $ticket): RedirectResponse
    {
        $ticket->update(['statut' => 'annule']);

        return back()->with('success', "Ticket {$ticket->code_ticket} annulé.");
    }

    /** Supprime définitivement une ligne de ticket (quel que soit son statut). */
    public function destroyTicket(FestyTicket $ticket): RedirectResponse
    {
        $code = $ticket->code_ticket;
        $ticket->delete();

        return back()->with('success', "Ticket {$code} supprimé définitivement.");
    }

    /** Recherche d'utilisateurs pour l'activation manuelle d'un ticket. */
    public function rechercheUsers(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")))
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'last_name', 'email', 'phone'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => trim($u->name.' '.($u->last_name ?? '')),
                'email' => $u->email,
                'phone' => $u->phone,
            ]);

        return response()->json(['users' => $users]);
    }

    /**
     * Active un ticket pour un utilisateur (paiement reçu hors ligne) : le ticket
     * est marqué payé, envoyé par e-mail au client et les admins sont notifiés.
     */
    public function activerTicket(Request $request, FestyTickets $service): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'in:participant,fan'],
            'festy_team_id' => ['nullable', 'integer', 'exists:festy_teams,id'],
            'montant' => ['nullable', 'integer', 'min:0', 'max:10000000'],
        ]);

        $prix = FestySetting::actuel()->prixTicket($validated['type']);
        $user = User::findOrFail($validated['user_id']);

        // Équipe imposée : on y inscrit aussi l'utilisateur (groupe WhatsApp, page Festy).
        if (! empty($validated['festy_team_id'])) {
            $team = FestyTeam::find($validated['festy_team_id']);
            if ($team) {
                $service->inscrireEquipe($user, $team);
            }
        }

        $ticket = FestyTicket::create([
            'reference' => 'FST_'.strtoupper(Str::random(12)),
            'code_ticket' => $service->genererCode(),
            'user_id' => $user->id,
            'festy_team_id' => $validated['festy_team_id'] ?? null,
            'type' => $prix['type'],
            'montant' => $validated['montant'] ?? $prix['montant'],
            'devise' => 'XAF',
            'promo' => $prix['promo'],
            'moyen' => 'offert',
            'statut' => 'en_attente',
            'payment_country' => 'CM',
        ]);

        $envoye = $service->finaliser($ticket, auth()->id());

        return $envoye
            ? back()->with('success', "Ticket {$ticket->code_ticket} activé pour {$user->name} — envoyé par e-mail.")
            : back()->with('error', "Ticket {$ticket->code_ticket} activé pour {$user->name}, mais l'e-mail n'a pas pu partir. Configurez les Réglages e-mail puis cliquez « Renvoyer l'e-mail ».");
    }

    public function storeTeam(Request $request): RedirectResponse
    {
        FestyTeam::create($this->validerTeam($request) + [
            'position' => (int) FestyTeam::max('position') + 1,
        ]);

        return back()->with('success', 'Équipe ajoutée.');
    }

    public function updateTeam(Request $request, FestyTeam $team): RedirectResponse
    {
        $data = $this->validerTeam($request);

        if ($request->hasFile('image')) {
            $this->deletePublicFile($team->image);
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/festy', 'team');
        }

        $team->update($data);

        return back()->with('success', 'Équipe mise à jour.');
    }

    public function destroyTeam(FestyTeam $team): RedirectResponse
    {
        $this->deletePublicFile($team->image);
        $team->delete();

        return back()->with('success', 'Équipe supprimée.');
    }

    /** Liste des inscrits, filtrable par équipe. */
    public function registrations(Request $request): Response
    {
        $teamId = $request->integer('equipe') ?: null;

        $query = FestyRegistration::with('team')->latest();
        if ($teamId) {
            $query->where('festy_team_id', $teamId);
        }

        $inscrits = $query->get()->map(fn (FestyRegistration $r) => [
            'id' => $r->id,
            'nom' => $r->nom,
            'prenom' => $r->prenom,
            'telephone' => $r->telephone,
            'email' => $r->email,
            'ville' => $r->ville,
            'quartier' => $r->quartier,
            'festy_team_id' => $r->festy_team_id,
            'equipe' => $r->team?->nom,
            'ip' => $r->ip,
            'date' => $r->created_at->isoFormat('D MMM YYYY [à] HH:mm'),
        ]);

        return Inertia::render('admin/festy/inscrits', [
            'inscrits' => $inscrits,
            'equipes' => FestyTeam::orderBy('position')->get(['id', 'nom'])
                ->map(fn ($t) => ['id' => $t->id, 'nom' => $t->nom]),
            'filtre' => $teamId,
            'total' => FestyRegistration::count(),
            'par_equipe' => FestyTeam::orderBy('position')
                ->withCount('registrations')
                ->get()
                ->map(fn ($t) => ['nom' => $t->nom, 'couleur' => $t->couleur, 'membres' => $t->registrations_count]),
        ]);
    }

    /** Modifie un inscrit (équipe, coordonnées). */
    public function updateRegistration(Request $request, FestyRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'festy_team_id' => ['required', 'integer', 'exists:festy_teams,id'],
            'nom' => ['nullable', 'string', 'max:160'],
            'prenom' => ['nullable', 'string', 'max:120'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'ville' => ['nullable', 'string', 'max:120'],
            'quartier' => ['nullable', 'string', 'max:160'],
        ]);

        $registration->update($validated);

        return back()->with('success', 'Inscrit mis à jour.');
    }

    /** Supprime un inscrit. */
    public function destroyRegistration(FestyRegistration $registration): RedirectResponse
    {
        $registration->delete();

        return back()->with('success', 'Inscrit supprimé.');
    }

    /**
     * Bloque et supprime définitivement le compte lié à cet inscrit
     * (faux comptes / doublons). Repli : blocage si la suppression échoue.
     */
    public function destroyAccount(FestyRegistration $registration): RedirectResponse
    {
        $user = $registration->user_id ? User::find($registration->user_id) : null;

        $registration->delete();

        if (! $user) {
            return back()->with('success', 'Inscription supprimée (aucun compte lié).');
        }

        $nom = trim($user->name.' '.($user->last_name ?? ''));

        try {
            $user->delete();

            return back()->with('success', "Compte de {$nom} supprimé définitivement.");
        } catch (\Throwable $e) {
            // Repli : on bloque le compte s'il ne peut pas être supprimé.
            $user->update(['valid' => 0, 'confirmed' => 0, 'is_blocked' => 1]);

            return back()->with('success', "Compte de {$nom} bloqué (suppression impossible).");
        }
    }

    /** Bannit l'adresse IP d'un inscrit (faux comptes / spam). */
    public function bannirIp(Request $request, FestyRegistration $registration): RedirectResponse
    {
        if (! $registration->ip) {
            return back()->withErrors(['ip' => 'Aucune IP enregistrée pour cet inscrit.']);
        }

        if ($registration->ip === $request->ip()) {
            return back()->withErrors(['ip' => 'Vous ne pouvez pas bannir votre propre adresse IP.']);
        }

        BannedIp::firstOrCreate(
            ['ip' => $registration->ip],
            ['raison' => 'Festy — '.($registration->nom ?? 'inscrit').' ('.($registration->email ?? '—').')'],
        );

        return back()->with('success', "IP {$registration->ip} bannie.");
    }

    /** Export CSV des inscrits (Excel). */
    public function export(Request $request)
    {
        $rows = FestyRegistration::with('team')->orderBy('festy_team_id')->latest()->get();

        $csv = "Équipe;Nom;Prénom;Téléphone;E-mail;Ville;Quartier;Date\n";
        foreach ($rows as $r) {
            $csv .= implode(';', [
                $r->team?->nom,
                str_replace(';', ',', (string) $r->nom),
                str_replace(';', ',', (string) $r->prenom),
                $r->telephone,
                $r->email,
                str_replace(';', ',', (string) $r->ville),
                str_replace(';', ',', (string) $r->quartier),
                $r->created_at->format('d/m/Y H:i'),
            ])."\n";
        }

        // BOM pour l'accentuation correcte dans Excel.
        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="inscrits-festy.csv"',
        ]);
    }

    private function validerTeam(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:80'],
            'trait' => ['nullable', 'string', 'max:120'],
            'couleur' => ['required', 'string', 'max:20'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'whatsapp_group' => ['nullable', 'string', 'max:500'],
            'actif' => ['boolean'],
        ]);
    }
}
