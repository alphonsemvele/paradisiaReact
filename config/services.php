<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Alerte à chaque nouvelle inscription (WhatsApp ou Telegram)
    'whatsapp' => [
        'driver'         => env('WHATSAPP_DRIVER', 'greenapi'), // greenapi | telegram | callmebot
        'phone'          => env('WHATSAPP_ALERT_PHONE'),        // ex : 237687984282 (ou un id de groupe ...@g.us)

        // Green API (passerelle WhatsApp)
        'green_instance' => env('GREENAPI_ID_INSTANCE'),
        'green_token'    => env('GREENAPI_API_TOKEN'),

        // Telegram (Bot API)
        'telegram_token' => env('TELEGRAM_BOT_TOKEN'),
        'telegram_chat'  => env('TELEGRAM_CHAT_ID'),

        // CallMeBot (relais)
        'callmebot_key'  => env('CALLMEBOT_APIKEY'),
    ],

    /*
    |---------------------------------------------------------------------------
    | Malapay — agrégateur de paiement
    |---------------------------------------------------------------------------
    | Les paiements passent par les portefeuilles Malapay tant que les
    | opérateurs mobile money des différents pays ne sont pas intégrés.
    | La clé est de la forme mpk_test_xxx (bac à sable) ou mpk_live_xxx.
    */
    'malapay' => [
        'url'     => env('MALAPAY_URL', 'https://mala-pay.com/api'),
        'key'     => env('MALAPAY_KEY'),
        'timeout' => env('MALAPAY_TIMEOUT', 15),
        // Site Malapay, distinct de l'API : sert à envoyer un investisseur
        // créer son portefeuille s'il n'en a pas encore.
        'site'    => env('MALAPAY_SITE_URL', 'https://mala-pay.com'),
    ],

];
