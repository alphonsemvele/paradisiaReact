<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/** Un e-mail de campagne envoyé à un utilisateur. */
class CampagneMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmailCampaign $campagne) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campagne->sujet,
            replyTo: ['contact@paradisia-africa.com'],
        );
    }

    /** En-tête d'anti-spam : Gmail apprécie le lien de désabonnement. */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<mailto:contact@paradisia-africa.com?subject=Desabonnement>',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.campagne',
            text: 'emails.texte.campagne',
        );
    }
}
