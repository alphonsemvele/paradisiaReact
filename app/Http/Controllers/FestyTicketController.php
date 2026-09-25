<?php

namespace App\Http\Controllers;

use App\Models\FestyRegistration;
use App\Models\FestySetting;
use App\Models\FestyTeam;
use App\Models\FestyTicket;
use App\Models\User;
use App\Services\FestyTickets;
use App\Services\MalaPay;
use App\Services\WhatsAppNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Achat d'un ticket PARADISIA FESTY.
 *
 * Deux formules (participant / fan) liées à une équipe, deux moyens de paiement :
 *   • MTN Mobile Money — automatique via MalaPay (opérateur « mtn » seul) ;
 *   • Orange Money — manuel : le client paie par code marchand, envoie la preuve
 *     sur WhatsApp, un admin valide ensuite le ticket.
 *
 * Le prix n'est jamais lu depuis le navigateur : il est recalculé côté serveur
 * à partir des réglages Festy (tarif normal ou promotionnel selon la date).
 */
class FestyTicketController extends Controller
{
    private const PAYS = 'CM';
    private const DEVISE = 'XAF';

    /** Échecs où MalaPay n'a rien enregistré : l'ébauche de ticket est supprimée. */
    private const ECHECS_SANS_TENTATIVE = ['MALAPAY_UNREACHABLE', 'MALAPAY_RATE_LIMITED', 'MALAPAY_NOT_CONFIGURED'];

    public function __construct(
        private readonly MalaPay $malapay,
        private readonly FestyTickets $tickets,
    ) {}

    public function page(Request $request): Response
    {
        $settings = FestySetting::actuel();
        $user = $request->user();

        if (! $user) {
            $request->session()->put('url.intended', route('festy.ticket'));
        }

        // Rapproche les paiements MTN restés en attente (onglet fermé trop tôt).
        if ($user && $this->malapay->estConfigure()) {
            FestyTicket::where('user_id', $user->id)
                ->where('statut', 'en_attente')->where('moyen', 'mtn')
                ->where('created_at', '>=', now()->subHours(6))
                ->get()
                ->each(fn (FestyTicket $t) => $this->tickets->reconcilier($t, $this->malapay));
        }

        $equipeActuelle = null;
        if ($user) {
            $equipe = $this->tickets->equipeUtilisateur($user);
            if ($equipe) {
                $places = $this->tickets->placesParticipant($equipe->id);
                $equipeActuelle = [
                    'id' => $equipe->id,
                    'nom' => $equipe->nom,
                    'couleur' => $equipe->couleur,
                    'whatsapp' => $equipe->whatsapp_group,
                    'places_restantes' => $places['restantes'],
                    'complet' => $places['complet'],
                ];
            }
        }

        $equipes = FestyTeam::where('actif', true)->orderBy('position')->get()
            ->map(function (FestyTeam $t) {
                $places = $this->tickets->placesParticipant($t->id);

                return [
                    'id' => $t->id,
                    'nom' => $t->nom,
                    'trait' => $t->trait,
                    'couleur' => $t->couleur,
                    'places_restantes' => $places['restantes'],
                    'complet' => $places['complet'],
                ];
            });

        $tickets = $user
            ? FestyTicket::with('team')->where('user_id', $user->id)->where('statut', 'paye')
                ->latest()->get()->map(fn (FestyTicket $t) => $this->presenter($t))->values()
            : collect();

        $enAttente = $user
            ? FestyTicket::where('user_id', $user->id)->where('statut', 'en_attente')
                ->where('moyen', 'om_manuel')->latest()->first()
            : null;

        return Inertia::render('festy/ticket', [
            'festy' => [
                'titre' => $settings->titre,
                'date_label' => $settings->date_label,
            ],
            'prix' => [
                'participant' => $settings->prixTicket('participant'),
                'fan' => $settings->prixTicket('fan'),
            ],
            'promo_fin' => $settings->enPromo() ? $settings->promo_fin?->format('d/m/Y') : null,
            'places_limite' => (int) ($settings->places_participant_equipe ?? 20),
            'moi' => $user ? [
                'nom' => trim($user->name.' '.($user->last_name ?? '')),
                'telephone' => $user->phone,
                'email' => $user->email,
            ] : null,
            'equipe' => $equipeActuelle,
            'equipes' => $equipes,
            'tickets' => $tickets,
            'en_attente' => $enAttente ? $this->presenter($enAttente) : null,
            'om' => [
                'ussd' => config('services.festy.om_ussd', '#150*47#'),
                'code_marchand' => config('services.festy.om_code', '1023095'),
                'whatsapp' => config('services.festy.whatsapp', '237687984282'),
            ],
            'mtn_disponible' => $this->malapay->estConfigure(),
        ]);
    }

    /** Paiement MTN Mobile Money (automatique via MalaPay). */
    public function payerMobile(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour prendre un ticket.'], 401);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:participant,fan'],
            'telephone' => ['required', 'string', 'max:20'],
            'festy_team_id' => ['nullable', 'integer', 'exists:festy_teams,id'],
        ]);

        $user = Auth::user();

        // Participant : équipe requise et place disponible (20 par équipe).
        $equipe = $this->equipePourTicket($user, $validated['type'], $validated['festy_team_id'] ?? null);
        if (! $equipe['ok']) {
            return response()->json(['ok' => false, 'message' => $equipe['message']], 422);
        }

        $prix = FestySetting::actuel()->prixTicket($validated['type']);
        $montant = $prix['montant'];
        $reference = 'FST_'.strtoupper(Str::random(12));

        $ticket = FestyTicket::create([
            'reference' => $reference,
            'code_ticket' => $this->genererCode(),
            'user_id' => $user->id,
            'festy_team_id' => $equipe['teamId'],
            'type' => $prix['type'],
            'montant' => $montant,
            'devise' => self::DEVISE,
            'promo' => $prix['promo'],
            'moyen' => 'mtn',
            'statut' => 'en_attente',
            'payment_country' => self::PAYS,
            'telephone' => $validated['telephone'],
        ]);

        $resultat = $this->malapay->payerMobile(
            reference: $reference,
            montant: (float) $montant,
            devise: self::DEVISE,
            pays: self::PAYS,
            operateur: 'mtn',
            telephone: $validated['telephone'],
            description: 'Ticket '.$ticket->typeLibelle().' — PARADISIA FESTY',
            service: 'festy',
            urlRetour: url('/festy/ticket?ref='.$reference),
            clientNom: $user->name,
            clientEmail: $user->email,
        );

        if (! $resultat['ok']) {
            $this->conclureEchec($ticket, $resultat['code']);

            return response()->json([
                'ok' => false,
                'code' => $resultat['code'],
                'message' => $resultat['message'],
            ], $this->statutHttp($resultat['code']));
        }

        $donnees = $resultat['data'] ?? [];
        $aPayer = (float) ($donnees['montant_a_payer'] ?? $montant);

        if (! empty($donnees['reference_paiement'] ?? null)) {
            $ticket->update(['ref_paiement_api' => $donnees['reference_paiement']]);
        }

        return response()->json([
            'ok' => true,
            'reference' => $reference,
            'montant_a_payer' => $aPayer,
            'montant_formate' => number_format($aPayer, 0, ',', ' ').' FCFA',
            'url_paiement' => $donnees['url_paiement'] ?? null,
            'code_ussd' => $donnees['code_ussd'] ?? null,
            'message' => $donnees['instruction'] ?? 'Finalisez le paiement MTN sur votre téléphone pour valider votre ticket.',
        ], 202);
    }

    /** Réservation d'un ticket payé par Orange Money (validation manuelle admin). */
    public function commanderManuel(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour prendre un ticket.'], 401);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:participant,fan'],
            'festy_team_id' => ['nullable', 'integer', 'exists:festy_teams,id'],
        ]);

        $user = Auth::user();

        $equipe = $this->equipePourTicket($user, $validated['type'], $validated['festy_team_id'] ?? null);
        if (! $equipe['ok']) {
            return response()->json(['ok' => false, 'message' => $equipe['message']], 422);
        }

        $prix = FestySetting::actuel()->prixTicket($validated['type']);
        $reference = 'FST_'.strtoupper(Str::random(12));

        $ticket = FestyTicket::create([
            'reference' => $reference,
            'code_ticket' => $this->genererCode(),
            'user_id' => $user->id,
            'festy_team_id' => $equipe['teamId'],
            'type' => $prix['type'],
            'montant' => $prix['montant'],
            'devise' => self::DEVISE,
            'promo' => $prix['promo'],
            'moyen' => 'om_manuel',
            'statut' => 'en_attente',
            'payment_country' => self::PAYS,
        ]);

        // Alerte les admins qu'un paiement Orange Money est à vérifier.
        WhatsAppNotifier::send(sprintf(
            "🟠 FESTY — Ticket %s à VALIDER (Orange Money)\nClient : %s\nMontant : %s FCFA\nRéf : %s\nValidez-le dans l'admin dès réception de la preuve.",
            $ticket->typeLibelle(),
            $user->name,
            number_format($prix['montant'], 0, ',', ' '),
            $reference,
        ), url('/admin/festy/tickets'));

        return response()->json([
            'ok' => true,
            'reference' => $reference,
            'montant' => $prix['montant'],
            'montant_formate' => number_format($prix['montant'], 0, ',', ' ').' FCFA',
            'message' => 'Ticket réservé. Effectuez le paiement Orange Money puis envoyez la preuve sur WhatsApp pour le recevoir.',
        ], 202);
    }

    /** Suivi d'un paiement (polling MTN ; les paiements Orange restent en attente admin). */
    public function statut(string $reference): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous.'], 401);
        }

        $ticket = FestyTicket::where('reference', $reference)->where('user_id', Auth::id())->first();

        if (! $ticket) {
            return response()->json(['ok' => false, 'message' => 'Ticket introuvable.'], 404);
        }

        if ($ticket->statut !== 'en_attente') {
            return response()->json([
                'ok' => true,
                'statut' => $ticket->statut,
                'termine' => true,
                'reussi' => $ticket->statut === 'paye',
            ]);
        }

        // Orange Money manuel : validation par un admin, rien à interroger.
        if ($ticket->moyen === 'om_manuel') {
            return response()->json(['ok' => true, 'statut' => 'en_attente', 'termine' => false]);
        }

        $resultat = $this->malapay->statutMobile($reference);

        if (! $resultat['ok']) {
            return response()->json(['ok' => true, 'statut' => 'en_attente', 'termine' => false]);
        }

        $statut = $resultat['data']['statut'] ?? 'en_attente';

        if ($statut === 'reussi') {
            $this->tickets->finaliser($ticket);

            return response()->json([
                'ok' => true,
                'statut' => 'paye',
                'termine' => true,
                'reussi' => true,
                'message' => 'Paiement confirmé : votre ticket vous a été envoyé par e-mail.',
            ]);
        }

        if (in_array($statut, ['echoue', 'annule', 'expire'], true)) {
            $ticket->update(['statut' => 'echoue', 'error_code' => strtoupper($statut)]);

            return response()->json([
                'ok' => true,
                'statut' => 'echoue',
                'termine' => true,
                'reussi' => false,
                'message' => $statut === 'annule'
                    ? 'Le paiement a été annulé.'
                    : 'Le paiement MTN n\'a pas abouti. Vous pouvez réessayer.',
            ]);
        }

        return response()->json(['ok' => true, 'statut' => 'en_attente', 'termine' => false]);
    }

    /**
     * Choix / changement d'équipe après paiement : inscrit l'utilisateur à
     * l'équipe et y rattache ses tickets encore sans équipe.
     */
    public function choisirEquipe(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return back()->with('error', 'Connectez-vous.');
        }

        $validated = $request->validate([
            'festy_team_id' => ['required', 'integer', 'exists:festy_teams,id'],
            'ville' => ['nullable', 'string', 'max:120'],
            'quartier' => ['nullable', 'string', 'max:160'],
        ]);

        $team = FestyTeam::where('id', $validated['festy_team_id'])->where('actif', true)->first();

        if (! $team) {
            return back()->withErrors(['festy_team_id' => 'Équipe indisponible.']);
        }

        // Inscription à l'équipe (créée ou mise à jour).
        $inscription = FestyRegistration::where('user_id', $user->id)->first();

        if ($inscription) {
            $inscription->update(array_filter([
                'festy_team_id' => $team->id,
                'ville' => $validated['ville'] ?? null,
                'quartier' => $validated['quartier'] ?? null,
            ], fn ($v) => $v !== null));
        } else {
            try {
                FestyRegistration::create([
                    'festy_team_id' => $team->id,
                    'user_id' => $user->id,
                    'nom' => $user->name,
                    'prenom' => $user->last_name,
                    'telephone' => $user->phone,
                    'email' => $user->email,
                    'ville' => $validated['ville'] ?? $user->ville ?? null,
                    'quartier' => $validated['quartier'] ?? null,
                    'ip' => $request->ip(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Doublon de téléphone : l'essentiel (rattacher le ticket) se fait plus bas.
            }
        }

        // Rattache les tickets payés encore sans équipe.
        FestyTicket::where('user_id', $user->id)->whereNull('festy_team_id')
            ->update(['festy_team_id' => $team->id]);

        return back()->with('success', "Tu fais partie de l'équipe {$team->nom} ! 🎉");
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    /**
     * Résout l'équipe d'un ticket et vérifie la disponibilité pour un
     * participant (20 places par équipe). Un fan n'est pas limité.
     *
     * @return array{ok:bool, teamId?:int|null, message?:string}
     */
    private function equipePourTicket(User $user, string $type, ?int $festyTeamId): array
    {
        // Équipe visée : celle demandée, sinon celle déjà rejointe.
        $teamId = $festyTeamId ?: $this->tickets->equipeUtilisateur($user)?->id;

        if ($type !== 'participant') {
            return ['ok' => true, 'teamId' => $teamId];
        }

        if (! $teamId) {
            return ['ok' => false, 'message' => 'Choisis ton équipe pour un ticket participant.'];
        }

        if ($this->tickets->placesParticipant($teamId)['complet']) {
            $nom = FestyTeam::find($teamId)?->nom;

            return ['ok' => false, 'message' => "L'équipe {$nom} est complète (places participants épuisées). Choisis une autre équipe."];
        }

        return ['ok' => true, 'teamId' => $teamId];
    }

    /** @return array<string, mixed> */
    private function presenter(FestyTicket $t): array
    {
        return [
            'id' => $t->id,
            'reference' => $t->reference,
            'code' => $t->code_ticket,
            'type' => $t->type,
            'type_libelle' => $t->typeLibelle(),
            'montant' => $t->montant,
            'promo' => $t->promo,
            'statut' => $t->statut,
            'moyen' => $t->moyen,
            'equipe' => $t->team?->nom,
            'couleur' => $t->team?->couleur,
            'whatsapp' => $t->team?->whatsapp_group,
            'titulaire' => $t->user?->name ?? $this->moiNom(),
            'date' => $t->created_at?->isoFormat('D MMM YYYY'),
            'paye_le' => $t->paid_at?->isoFormat('D MMM YYYY [à] HH:mm'),
        ];
    }

    private function moiNom(): string
    {
        $u = Auth::user();

        return $u ? trim($u->name.' '.($u->last_name ?? '')) : '';
    }

    private function genererCode(): string
    {
        do {
            $code = 'FST-'.strtoupper(Str::random(6));
        } while (FestyTicket::where('code_ticket', $code)->exists());

        return $code;
    }

    private function conclureEchec(FestyTicket $ticket, string $code): void
    {
        if (in_array($code, self::ECHECS_SANS_TENTATIVE, true)) {
            $ticket->delete();

            return;
        }

        $ticket->update(['statut' => 'echoue', 'error_code' => $code]);
    }

    private function statutHttp(string $code): int
    {
        return match ($code) {
            'MALAPAY_RATE_LIMITED' => 429,
            'MALAPAY_UNREACHABLE', 'MALAPAY_NOT_CONFIGURED', 'PROJECT_INACTIVE' => 503,
            default => 422,
        };
    }
}
