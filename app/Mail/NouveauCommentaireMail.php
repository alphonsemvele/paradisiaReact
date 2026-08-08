<?php

namespace App\Mail;

use App\Models\Comment;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** E-mail à l'auteur d'une publication quand quelqu'un la commente. */
class NouveauCommentaireMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $destinataire,
        public User $auteurCommentaire,
        public Publication $publication,
        public Comment $commentaire,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->auteurCommentaire->name.' a commenté votre publication — PARADISIA');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nouveau-commentaire',
            text: 'emails.texte.nouveau-commentaire',
        );
    }
}
