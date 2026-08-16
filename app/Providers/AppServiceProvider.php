<?php

namespace App\Providers;

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
        // Délivrabilité : l'expéditeur DOIT être sur le domaine (sinon Gmail
        // classe en spam pour cause d'alignement SPF/DKIM). Si le .env est resté
        // sur le placeholder, on force un expéditeur du domaine.
        $from = (string) config('mail.from.address');
        if ($from === '' || str_contains($from, 'example.com')) {
            Mail::alwaysFrom('no-reply@paradisia-africa.com', config('mail.from.name') ?: 'Paradisia');
        }
    }
}
