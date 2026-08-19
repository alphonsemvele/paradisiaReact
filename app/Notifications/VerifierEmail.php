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
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirme ton compte Paradisia 🍍')
            ->greeting('Bienvenue sur Paradisia !')
            ->line('Merci pour ton inscription. Confirme ton adresse e-mail en cliquant sur le bouton ci-dessous.')
            ->action('Confirmer mon compte', $url)
            ->line('Ton compte est déjà actif — cette confirmation nous aide juste à vérifier ton adresse.')
            ->salutation("À très vite,\nL'équipe Paradisia");
    }
}
