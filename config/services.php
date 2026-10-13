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

    // Customer-facing API endpoint that drops cached data when the admin saves.
    'storefront' => [
        'invalidate_url' => env('STOREFRONT_INVALIDATE_URL'),
        'invalidate_secret' => env('STOREFRONT_INVALIDATE_SECRET'),
    ],

    // IndexNow: pings Bing/Yandex/Naver/Seznam when catalogue pages change. The key is public by design
    // (it is served at https://<host>/<key>.txt). Enabled by default wherever storefront sync is configured.
    'indexnow' => [
        'enabled' => env('INDEXNOW_ENABLED'),
        'key' => env('INDEXNOW_KEY', '5e18a089fd8877586675bdb039d42ed2'),
        'host' => env('INDEXNOW_HOST', 'fatafatsewa.com'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
