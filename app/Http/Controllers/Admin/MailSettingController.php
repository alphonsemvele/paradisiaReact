<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/** Réglages e-mail (SMTP) modifiables depuis l'admin, sans toucher au .env. */
class MailSettingController extends Controller
{
    public function index(): Response
    {
        $s = MailSetting::actuel();

        return Inertia::render('admin/reglages/email', [
            'reglages' => [
                'actif' => (bool) $s->actif,
                'mailer' => $s->mailer,
                'host' => $s->host,
                'port' => $s->port,
                'username' => $s->username,
                'a_mot_de_passe' => ! empty($s->password),
                'encryption' => $s->encryption,
                'from_address' => $s->from_address,
                'from_name' => $s->from_name,
            ],
            // Config réellement active (utile pour diagnostiquer).
            'actuel' => [
                'transport' => config('mail.default'),
                'from' => config('mail.from.address'),
                'host' => config('mail.mailers.smtp.host'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'actif' => ['boolean'],
            'mailer' => ['required', 'in:smtp,sendmail'],
            'host' => ['nullable', 'string', 'max:180'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:180'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:ssl,tls'],
            'from_address' => ['nullable', 'email', 'max:180'],
            'from_name' => ['nullable', 'string', 'max:120'],
        ]);

        $s = MailSetting::actuel();

        $s->actif = (bool) ($data['actif'] ?? false);
        $s->mailer = $data['mailer'];
        $s->host = $data['host'] ?? null;
        $s->port = $data['port'] ?? 465;
        $s->username = $data['username'] ?? null;
        $s->encryption = $data['encryption'] ?? null;
        $s->from_address = $data['from_address'] ?? null;
        $s->from_name = $data['from_name'] ?? null;

        // On ne remplace le mot de passe que s'il est fourni (sinon on garde l'ancien).
        if (! empty($data['password'])) {
            $s->password = $data['password'];
        }

        $s->save();

        return back()->with('success', 'Réglages e-mail enregistrés.');
    }

    /** Envoie un e-mail de test avec les réglages ENREGISTRÉS. */
    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        try {
            Mail::raw(
                "Test d'envoi Paradisia via SMTP.\n\nTransport : ".config('mail.default').
                "\nExpéditeur : ".config('mail.from.address')."\nDate : ".now()->format('d/m/Y H:i:s'),
                fn ($m) => $m->to($data['email'])->subject('Test e-mail — Paradisia')
            );

            return back()->with('success', "E-mail de test envoyé à {$data['email']}. Vérifie la réception (et les spams).");
        } catch (\Throwable $e) {
            return back()->withErrors(['test' => get_class($e).' : '.$e->getMessage()]);
        }
    }
}
