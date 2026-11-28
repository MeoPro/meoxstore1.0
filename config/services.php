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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'admin_ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TELEGRAM_ADMIN_IDS', '')),
        ))),
        'channel_id' => env('TELEGRAM_CHANNEL_ID'),
        'post_chat' => env('TELEGRAM_POST_CHAT'),
        'post_instagram' => env('TELEGRAM_POST_INSTAGRAM'),
    ],

    'google_translate' => [
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'location' => env('GOOGLE_TRANSLATE_LOCATION', 'global'),
    ],

    'google_maps' => [
        'profile_url' => env('GOOGLE_MAPS_PROFILE_URL', 'https://maps.app.goo.gl/EDWrmX65TUEFuuKJ6'),
        'embed_url' => env('GOOGLE_MAPS_EMBED_URL', 'https://www.google.com/maps?q=-8.5723901%2C115.3047109&z=17&output=embed'),
    ],

    'google_business_profile' => [
        'client_id' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_BUSINESS_PROFILE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_BUSINESS_PROFILE_REDIRECT_URI'),
    ],

];
