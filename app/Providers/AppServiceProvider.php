<?php

namespace App\Providers;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;
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
        // Réglages e-mail saisis dans l'admin (SMTP) : ils priment sur le .env.
        $this->appliquerReglagesMail();

        // Délivrabilité : l'expéditeur DOIT être sur le domaine (sinon Gmail
        // classe en spam pour cause d'alignement SPF/DKIM). Si le .env est resté
        // sur le placeholder, on force un expéditeur du domaine.
        $from = (string) config('mail.from.address');
        if ($from === '' || str_contains($from, 'example.com')) {
            Mail::alwaysFrom('no-reply@paradisia-africa.com', config('mail.from.name') ?: 'Paradisia');
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
                'mail.from.address' => $s->from_address ?: config('mail.from.address'),
                'mail.from.name' => $s->from_name ?: config('mail.from.name'),
            ]);
        } catch (\Throwable $e) {
            // Table absente (avant migration) ou erreur : on garde la config .env.
        }
    }
}
