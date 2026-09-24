<?php

namespace App\Mail;

use App\Models\FestyTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Ticket PARADISIA FESTY envoyé à son titulaire après paiement. */
class FestyTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FestyTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre ticket Paradisia Festy est confirmé',
        );
    }

    public function content(): Content
    {
        // Version texte jointe : un e-mail HTML seul est un signal de spam.
        return new Content(
            view: 'emails.festy-ticket',
            text: 'emails.texte.festy-ticket',
        );
    }
}
