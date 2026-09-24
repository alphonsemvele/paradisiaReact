<?php

namespace App\Services;

use App\Mail\FestyTicketMail;
use App\Models\FestyRegistration;
use App\Models\FestySetting;
use App\Models\FestyTeam;
use App\Models\FestyTicket;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Cycle de vie d'un ticket Festy, partagé entre le paiement public (MTN) et la
 * validation manuelle par l'admin (Orange Money) : on rattache l'équipe déjà
 * choisie, on envoie le ticket par e-mail et on alerte les administrateurs.
 */
class FestyTickets
{
    /**
     * Marque un ticket comme payé, l'envoie par e-mail et notifie les admins.
     * Idempotent : un ticket déjà payé n'est pas retraité (ni ré-envoyé).
     *
     * @return bool l'e-mail du ticket a-t-il bien été envoyé ?
     */
    public function finaliser(FestyTicket $ticket, ?int $validePar = null): bool
    {
        if ($ticket->statut === 'paye') {
            return false;
        }

        $ticket->loadMissing('user');

        // Équipe du ticket : celle déjà portée, sinon celle rejointe par l'acheteur.
        $teamId = $ticket->festy_team_id ?? $this->equipeUtilisateur($ticket->user)?->id;

        $ticket->update([
            'statut' => 'paye',
            'paid_at' => now(),
            'festy_team_id' => $teamId,
            'valide_par' => $validePar,
        ]);

        // Inscrit l'acheteur à l'équipe du ticket (groupe WhatsApp + page Festy).
        if ($teamId && $ticket->user) {
            $team = FestyTeam::find($teamId);
            if ($team) {
                $this->inscrireEquipe($ticket->user, $team);
            }
        }

        $frais = $ticket->fresh(['team', 'user']);
        $envoye = $this->envoyerTicket($frais);
        $this->notifierAdmins($frais);

        return $envoye;
    }

    /** Renvoie le ticket par e-mail (bouton admin). */
    public function renvoyer(FestyTicket $ticket): bool
    {
        return $this->envoyerTicket($ticket->fresh(['team', 'user']));
    }

    /**
     * Réconcilie un paiement MTN resté « en attente » (onglet fermé avant la fin)
     * avec l'état constaté chez MalaPay. Appelé au chargement de la page.
     */
    public function reconcilier(FestyTicket $ticket, MalaPay $malapay): void
    {
        if ($ticket->statut !== 'en_attente' || $ticket->moyen !== 'mtn') {
            return;
        }

        $resultat = $malapay->statutMobile($ticket->reference);

        if (! $resultat['ok']) {
            return;
        }

        $statut = $resultat['data']['statut'] ?? 'en_attente';

        if ($statut === 'reussi') {
            $this->finaliser($ticket);

            return;
        }

        if (in_array($statut, ['echoue', 'annule', 'expire'], true)) {
            $ticket->update(['statut' => 'echoue', 'error_code' => strtoupper($statut)]);
        }
    }

    /** Équipe Festy déjà rejointe par l'utilisateur, le cas échéant. */
    public function equipeUtilisateur(?User $user): ?FestyTeam
    {
        if (! $user) {
            return null;
        }

        return FestyRegistration::with('team')->where('user_id', $user->id)->first()?->team;
    }

    /**
     * Places « participant » d'une équipe : limite, occupées (payées + réservées
     * en attente) et restantes. Les tickets « fan » ne sont pas limités.
     *
     * @return array{limite:int, occupees:int, restantes:int, complet:bool}
     */
    public function placesParticipant(int $teamId): array
    {
        $limite = (int) (FestySetting::actuel()->places_participant_equipe ?? 20);

        $occupees = FestyTicket::where('type', 'participant')
            ->where('festy_team_id', $teamId)
            ->whereIn('statut', ['paye', 'en_attente'])
            ->count();

        return [
            'limite' => $limite,
            'occupees' => $occupees,
            'restantes' => max(0, $limite - $occupees),
            'complet' => $occupees >= $limite,
        ];
    }

    /** Code lisible et unique imprimé sur le ticket. */
    public function genererCode(): string
    {
        do {
            $code = 'FST-'.strtoupper(Str::random(6));
        } while (FestyTicket::where('code_ticket', $code)->exists());

        return $code;
    }

    /** Inscrit (ou rebascule) un utilisateur dans une équipe. */
    public function inscrireEquipe(User $user, FestyTeam $team): void
    {
        $inscription = FestyRegistration::where('user_id', $user->id)->first();

        if ($inscription) {
            $inscription->update(['festy_team_id' => $team->id]);

            return;
        }

        try {
            FestyRegistration::create([
                'festy_team_id' => $team->id,
                'user_id' => $user->id,
                'nom' => $user->name,
                'prenom' => $user->last_name,
                'telephone' => $user->phone,
                'email' => $user->email,
                'ville' => $user->ville,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Doublon de téléphone : sans importance ici, le ticket porte l'équipe.
        }
    }

    /**
     * Envoie le ticket par e-mail. Un échec d'envoi ne remet jamais en cause le
     * paiement, mais il est remonté (retour false) pour prévenir l'admin.
     */
    private function envoyerTicket(FestyTicket $ticket): bool
    {
        if (! $ticket->user?->email) {
            return false;
        }

        try {
            Mail::to($ticket->user->email)->send(new FestyTicketMail($ticket));

            return true;
        } catch (\Throwable $e) {
            Log::error("Ticket Festy {$ticket->reference} non envoyé : ".$e->getMessage());

            return false;
        }
    }

    /** Alerte les administrateurs qu'un ticket a été payé. */
    private function notifierAdmins(FestyTicket $ticket): void
    {
        $moyen = match ($ticket->moyen) {
            'mtn' => 'MTN Mobile Money',
            'om_manuel' => 'Orange Money',
            'offert' => 'Activé par un admin',
            default => $ticket->moyen,
        };

        WhatsAppNotifier::send(sprintf(
            "🎟️ FESTY — Ticket %s PAYÉ\nClient : %s\nÉquipe : %s\nMontant : %s FCFA\nMoyen : %s\nCode : %s",
            $ticket->typeLibelle(),
            $ticket->user?->name ?? '—',
            $ticket->team?->nom ?? 'à choisir',
            number_format($ticket->montant, 0, ',', ' '),
            $moyen,
            $ticket->code_ticket,
        ));
    }
}
