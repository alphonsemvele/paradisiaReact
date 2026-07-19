<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmation envoyée à l'investisseur après validation de son paiement. */
class InvestissementConfirmeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre investissement est confirmé — PARADISIA',
        );
    }

    public function content(): Content
    {
        // Une version texte accompagne le HTML : un message HTML seul est un
        // signal de spam classique chez Gmail et Outlook.
        return new Content(
            view: 'emails.investissement-confirme',
            text: 'emails.texte.investissement-confirme',
        );
    }
}
