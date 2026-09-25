<?php

namespace App\Providers;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Interface et messages en français (le .env peut être resté sur « en »).
        app()->setLocale('fr');

        // Hébergement mutualisé (N0C) : le SSL est terminé en amont, donc PHP
        // voit souvent la requête en http. Les liens signés (confirmation de
        // compte…) sont eux générés en https via APP_URL : à la validation, les
        // deux URL diffèrent et Laravel renvoie 403 Forbidden. On force https en
        // production pour que génération ET validation utilisent le même schéma.
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Réglages e-mail saisis dans l'admin (SMTP) : ils priment sur le .env.
        $this->appliquerReglagesMail();

        // Délivrabilité : l'expéditeur DOIT être sur le domaine (sinon Gmail
        // classe en spam pour cause d'alignement SPF/DKIM). Une adresse « no-reply »
        // bâtit aussi moins de réputation qu'une vraie boîte surveillée : on
        // privilégie donc contact@ dès que le .env est resté sur un placeholder
        // ou un no-reply. Une adresse perso explicite reste, elle, respectée.
        $from = (string) config('mail.from.address');
        if ($from === '' || str_contains($from, 'example.com') || str_starts_with($from, 'no-reply@')) {
            $nom = config('mail.from.name') ?: 'Paradisia';
            // On met aussi à jour la config, pas seulement l'envoi : ainsi la page
            // Réglages e-mail affiche le bon expéditeur (contact@) et non le .env.
            config(['mail.from.address' => 'contact@paradisia-africa.com', 'mail.from.name' => $nom]);
            Mail::alwaysFrom('contact@paradisia-africa.com', $nom);
        }
    }

    /** Applique les réglages SMTP définis dans l'admin (sans toucher au .env). */
    private function appliquerReglagesMail(): void
    {
        try {
            $s = MailSetting::cache();
            if (! $s || ! $s->actif || ! $s->host) {
                return;
            }

            config([
                'mail.default' => $s->mailer ?: 'smtp',
                'mail.mailers.smtp.host' => $s->host,
                'mail.mailers.smtp.port' => (int) ($s->port ?: 465),
                'mail.mailers.smtp.username' => $s->username,
                'mail.mailers.smtp.password' => $s->password,
                'mail.mailers.smtp.encryption' => $s->encryption ?: null,
                // Laravel 11+ porte le chiffrement par le "scheme" (ssl → smtps).
                'mail.mailers.smtp.scheme' => $s->encryption === 'ssl' ? 'smtps' : null,
                'mail.from.address' => $s->from_address ?: config('mail.from.address'),
                'mail.from.name' => $s->from_name ?: config('mail.from.name'),
            ]);
        } catch (\Throwable $e) {
            // Table absente (avant migration) ou erreur : on garde la config .env.
        }
    }
}
