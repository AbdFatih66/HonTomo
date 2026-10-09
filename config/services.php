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

    // API fallback for the kanji-meaning glossary, used only when the
    // curated glossary has no entry — see
    // App\Services\Kanji\Translation\ApiFallbackMeaningTranslator and
    // docs/kanji-module.md section 3. Leaving DEEPL_API_KEY unset keeps
    // the whole pipeline exactly as it was: glossary-only, offline,
    // deterministic. Get a free-tier key at https://www.deepl.com/pro-api.
    'deepl' => [
        'key' => env('DEEPL_API_KEY'),
        'api_url' => env('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost'), '/').'/api/auth/google/callback'),
    ],

    // CAPTCHA on /register. Optional: leave both empty to disable (see App\Rules\Turnstile).
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Used by App\Services\GoogleTextToSpeechService and the
    // `chokai:generate-audio` artisan command. Get a key from
    // https://console.cloud.google.com/apis/credentials after enabling
    // "Cloud Text-to-Speech API" on a Google Cloud project.
    'google_tts' => [
        'key' => env('GOOGLE_TTS_API_KEY'),
    ],

];
