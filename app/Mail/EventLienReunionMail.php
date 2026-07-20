<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Envoi du lien de réunion aux inscrits, « le moment venu ». */
class EventLienReunionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public EventRegistration $inscription,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Lien de connexion : '.$this->event->titre.' — PARADISIA');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-lien',
            text: 'emails.texte.event-lien',
        );
    }
}
