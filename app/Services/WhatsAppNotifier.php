<?php

namespace App\Services;

use App\Models\NotificationRecipient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Alerte à chaque paiement / événement : envoyée sur WhatsApp/Telegram (selon
 * WHATSAPP_DRIVER) ET par e-mail au superadmin (services.superadmin_email).
 * Toute absence de config ou erreur est loguée sans jamais bloquer la requête.
 */
class WhatsAppNotifier
{
    /**
     * @param string      $message texte de l'alerte (la 1re ligne sert d'objet)
     * @param string|null $lien    URL admin pour aller traiter l'action (bouton)
     */
    public static function send(string $message, ?string $lien = null): void
    {
        // Différé après l'envoi de la réponse HTTP : les appels réseau ne
        // ralentissent jamais la requête de l'utilisateur.
        app()->terminating(function () use ($message, $lien) {
            self::envoyerWhatsApp($lien ? $message."\n".$lien : $message);
            self::notifierAdmins($message, $lien);
        });
    }

    /** Alerte WhatsApp/Telegram selon le driver configuré. */
    private static function envoyerWhatsApp(string $message): void
    {
        $driver = config('services.whatsapp.driver', 'greenapi');

        if ($missing = self::missingConfig($driver)) {
            Log::warning("Alerte WhatsApp NON envoyée : driver \"{$driver}\", config manquante dans .env : {$missing}. Message :\n{$message}");

            return;
        }

        try {
            match ($driver) {
                'greenapi'  => self::greenApi($message),
                'telegram'  => self::telegram($message),
                'callmebot' => self::callMeBot($message),
                default     => Log::warning("Alerte WhatsApp : driver inconnu \"{$driver}\""),
            };
            Log::info("Alerte WhatsApp envoyée via {$driver}.");
        } catch (\Throwable $e) {
            Log::warning('Alerte WhatsApp échouée : ' . $e->getMessage());
        }
    }

    /** Notifie par e-mail tous les destinataires admin (avec lien de traitement). */
    private static function notifierAdmins(string $message, ?string $lien): void
    {
        $emails = NotificationRecipient::emailsActifs();

        if (empty($emails)) {
            return;
        }

        try {
            $sujet = strtok($message, "\n") ?: 'Notification Paradisia';
            Mail::send(
                ['emails.admin-notification', 'emails.texte.admin-notification'],
                ['contenu' => $message, 'lien' => $lien, 'sujet' => $sujet],
                fn ($m) => $m->to($emails)->subject('[Paradisia] '.$sujet),
            );
            Log::info('Notification admin envoyée à '.implode(', ', $emails));
        } catch (\Throwable $e) {
            Log::warning('Notification admin échouée : '.$e->getMessage());
        }
    }

    /**
     * Renvoie la liste des variables .env absentes pour le driver choisi,
     * ou null si tout est en place.
     */
    private static function missingConfig(string $driver): ?string
    {
        $required = match ($driver) {
            'greenapi'  => ['WHATSAPP_ALERT_PHONE' => 'phone', 'GREENAPI_ID_INSTANCE' => 'green_instance', 'GREENAPI_API_TOKEN' => 'green_token'],
            'telegram'  => ['TELEGRAM_BOT_TOKEN' => 'telegram_token', 'TELEGRAM_CHAT_ID' => 'telegram_chat'],
            'callmebot' => ['WHATSAPP_ALERT_PHONE' => 'phone', 'CALLMEBOT_APIKEY' => 'callmebot_key'],
            default     => [],
        };

        $missing = collect($required)
            ->reject(fn ($key) => filled(config("services.whatsapp.{$key}")))
            ->keys()
            ->implode(', ');

        return $missing ?: null;
    }

    /**
     * Green API — passerelle WhatsApp (gratuit en dev, supporte numéros et groupes).
     * https://green-api.com
     */
    private static function greenApi(string $message): void
    {
        $instance = config('services.whatsapp.green_instance');
        $token    = config('services.whatsapp.green_token');
        $phone    = config('services.whatsapp.phone');

        if (! $instance || ! $token || ! $phone) {
            return;
        }

        // chatId : numéro -> 237xxxxxxxxx@c.us ; groupe -> id complet @g.us
        $chatId = str_contains($phone, '@') ? $phone : $phone . '@c.us';

        Http::timeout(10)->post(
            "https://api.green-api.com/waInstance{$instance}/sendMessage/{$token}",
            ['chatId' => $chatId, 'message' => $message]
        );
    }

    /**
     * Telegram — Bot API officielle (gratuit, fiable, fonctionne pour un groupe).
     * Créez un bot via @BotFather, ajoutez-le au groupe, récupérez le chat_id.
     */
    private static function telegram(string $message): void
    {
        $token  = config('services.whatsapp.telegram_token');
        $chatId = config('services.whatsapp.telegram_chat');

        if (! $token || ! $chatId) {
            return;
        }

        Http::timeout(10)->post(
            "https://api.telegram.org/bot{$token}/sendMessage",
            ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'Markdown']
        );
    }

    /**
     * CallMeBot — relais simple (clé obtenue via une activation manuelle).
     */
    private static function callMeBot(string $message): void
    {
        $phone  = config('services.whatsapp.phone');
        $apikey = config('services.whatsapp.callmebot_key');

        if (! $phone || ! $apikey) {
            return;
        }

        Http::timeout(8)->get('https://api.callmebot.com/whatsapp.php', [
            'phone'  => $phone,
            'text'   => $message,
            'apikey' => $apikey,
        ]);
    }
}
