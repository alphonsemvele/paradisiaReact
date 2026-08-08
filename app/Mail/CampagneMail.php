<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Un e-mail de campagne envoyé à un utilisateur. */
class CampagneMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmailCampaign $campagne) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campagne->sujet);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.campagne',
            text: 'emails.texte.campagne',
        );
    }
}
