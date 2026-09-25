<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CampagneMail;
use App\Mail\EventInscriptionMail;
use App\Mail\EventLienReunionMail;
use App\Mail\FestyTicketMail;
use App\Mail\InvestissementConfirmeMail;
use App\Mail\NouveauCommentaireMail;
use App\Models\Comment;
use App\Models\EmailCampaign;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\FestyTeam;
use App\Models\FestyTicket;
use App\Models\Payment;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Prévisualisation des e-mails : envoie n'importe quel modèle d'e-mail avec des
 * données d'exemple, sans avoir à créer un vrai utilisateur / paiement.
 */
class MailPreviewController extends Controller
{
    private const TYPES = [
        'verification' => 'Confirmation de compte (inscription)',
        'festy-ticket' => 'Ticket Festy',
        'investissement' => 'Investissement confirmé',
        'commentaire' => 'Nouveau commentaire',
        'event-inscription' => 'Inscription à un événement',
        'event-lien' => 'Lien de réunion (événement)',
        'campagne' => 'Campagne e-mailing',
        'admin-notification' => 'Notification admin',
    ];

    public function index(): Response
    {
        return Inertia::render('admin/reglages/email-test', [
            'types' => collect(self::TYPES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(self::TYPES))],
            'email' => ['required', 'email'],
        ]);

        $email = $data['email'];

        try {
            match ($data['type']) {
                'verification' => Mail::send('emails.verification', [
                    'url' => url('/verify-email/1/'.sha1('demo')),
                    'user' => $this->user(),
                ], fn ($m) => $m->to($email)->subject('[TEST] Confirme ton compte Paradisia')),

                'admin-notification' => Mail::send(
                    ['emails.admin-notification', 'emails.texte.admin-notification'],
                    [
                        'contenu' => "🎟️ FESTY — Ticket Participant PAYÉ\nClient : Loïc Test\nÉquipe : Ananas\nMontant : 5 000 FCFA\nMoyen : MTN Mobile Money\nCode : FST-DEMO12",
                        'lien' => url('/admin/festy/tickets'),
                        'sujet' => 'Notification admin (exemple)',
                    ],
                    fn ($m) => $m->to($email)->subject('[TEST] Notification admin'),
                ),

                'festy-ticket' => Mail::to($email)->send(new FestyTicketMail($this->ticket())),
                'investissement' => Mail::to($email)->send(new InvestissementConfirmeMail($this->payment())),
                'commentaire' => Mail::to($email)->send(new NouveauCommentaireMail($this->user('Loïc'), $this->user('Amina'), $this->publication(), $this->comment())),
                'event-inscription' => Mail::to($email)->send(new EventInscriptionMail($this->event(), $this->eventReg())),
                'event-lien' => Mail::to($email)->send(new EventLienReunionMail($this->event(), $this->eventReg())),
                'campagne' => Mail::to($email)->send(new CampagneMail($this->campagne())),
            };
        } catch (\Throwable $e) {
            return back()->with('error', "Échec de l'envoi : ".$e->getMessage());
        }

        return back()->with('success', 'E-mail de test « '.self::TYPES[$data['type']]." » envoyé à {$email}. Vérifie ta boîte (et les spams).");
    }

    /* ═══════════ Données d'exemple (en mémoire, non enregistrées) ═══════════ */

    private function user(string $nom = 'Loïc Test'): User
    {
        $u = new User();
        $u->name = $nom;
        $u->last_name = '';
        $u->email = 'exemple@paradisia-africa.com';

        return $u;
    }

    private function ticket(): FestyTicket
    {
        $team = new FestyTeam();
        $team->nom = 'Ananas';
        $team->couleur = '#F5B301';
        $team->whatsapp_group = 'https://chat.whatsapp.com/EXEMPLE';

        $t = new FestyTicket();
        $t->code_ticket = 'FST-DEMO12';
        $t->type = 'participant';
        $t->montant = 5000;
        $t->promo = true;
        $t->created_at = now();
        $t->paid_at = now();
        $t->setRelation('user', $this->user());
        $t->setRelation('team', $team);

        return $t;
    }

    private function payment(): Payment
    {
        $p = new Payment();
        $p->customer_name = 'Loïc Test';
        $p->share = 10;
        $p->total_amount = 50000;
        $p->currency = 'XAF';
        $p->ref = 'INV_DEMO123';
        $p->created_at = now();

        return $p;
    }

    private function publication(): Publication
    {
        $p = new Publication();
        $p->id = 1;

        return $p;
    }

    private function comment(): Comment
    {
        $c = new Comment();
        $c->body = "Vraiment super, bravo à toute l'équipe Paradisia ! 🍍";

        return $c;
    }

    private function event(): Event
    {
        $e = new Event();
        $e->titre = 'Assemblée des investisseurs Paradisia';
        $e->mode = 'en_ligne';
        $e->date_debut = now()->addDays(5);
        $e->message_confirmation = null;
        $e->lien_reunion = 'https://meet.google.com/exemple-lien';

        return $e;
    }

    private function eventReg(): EventRegistration
    {
        $r = new EventRegistration();
        $r->nom = 'Loïc';
        $r->profil = 'investisseur';
        $r->pays = 'Cameroun';

        return $r;
    }

    private function campagne(): EmailCampaign
    {
        $c = new EmailCampaign();
        $c->sujet = 'Nouveauté Paradisia : nos jus arrivent près de chez vous';
        $c->contenu = "Bonjour,\n\nDécouvrez notre nouvelle gamme de jus naturels d'ananas, pressés à froid et 100% naturels.\n\nÀ très vite,\nL'équipe Paradisia";

        return $c;
    }
}
