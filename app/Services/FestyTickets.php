<?php

namespace App\Services;

use App\Mail\FestyTicketMail;
use App\Models\FestyRegistration;
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
     */
    public function finaliser(FestyTicket $ticket, ?int $validePar = null): void
    {
        if ($ticket->statut === 'paye') {
            return;
        }

        $ticket->loadMissing('user');

        $ticket->update([
            'statut' => 'paye',
            'paid_at' => now(),
            // Rattache l'équipe déjà choisie par l'utilisateur, s'il en a une.
            'festy_team_id' => $ticket->festy_team_id ?? $this->equipeUtilisateur($ticket->user)?->id,
            'valide_par' => $validePar,
        ]);

        $frais = $ticket->fresh(['team', 'user']);
        $this->envoyerTicket($frais);
        $this->notifierAdmins($frais);
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

    /** Envoie le ticket par e-mail. Un échec d'envoi ne remet jamais en cause le paiement. */
    private function envoyerTicket(FestyTicket $ticket): void
    {
        if (! $ticket->user?->email) {
            return;
        }

        try {
            Mail::to($ticket->user->email)->send(new FestyTicketMail($ticket));
        } catch (\Throwable $e) {
            Log::error("Ticket Festy {$ticket->reference} non envoyé : ".$e->getMessage());
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
