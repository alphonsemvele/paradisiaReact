<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * E-mail de confirmation de compte (français). Le lien est une URL signée,
 * valable quelques jours, qui marque l'adresse comme vérifiée.
 */
class VerifierEmail extends BaseVerifyEmail
{
    /**
     * E-mail de confirmation, rendu avec le gabarit Paradisia (logo, couleurs,
     * pied de page). On n'utilise pas le template markdown par défaut, qui
     * ajoute un vilain bloc « copier-coller cette URL » en bas.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirme ton compte Paradisia')
            ->view('emails.verification', ['url' => $url, 'user' => $notifiable]);
    }
}
