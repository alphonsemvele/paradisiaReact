<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoie une alerte à chaque inscription.
 * Le fournisseur est choisi via WHATSAPP_DRIVER (greenapi | telegram | callmebot).
 * En cas d'absence de config ou d'erreur, on logue sans bloquer la requête.
 */
class WhatsAppNotifier
{
    public static function send(string $message): void
    {
        $driver = config('services.whatsapp.driver', 'greenapi');

        try {
            match ($driver) {
                'greenapi'  => self::greenApi($message),
                'telegram'  => self::telegram($message),
                'callmebot' => self::callMeBot($message),
                default     => null,
            };
        } catch (\Throwable $e) {
            Log::warning('Alerte inscription échouée : ' . $e->getMessage());
        }
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
