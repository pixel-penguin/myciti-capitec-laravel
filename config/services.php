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

    'frotcom' => [
        'base_url' => env('FROTCOM_BASE_URL'),
        'api_key' => env('FROTCOM_API_KEY'),
        'username' => env('FROTCOM_USERNAME'),
        'password' => env('FROTCOM_PASSWORD'),
        'token_ttl' => env('FROTCOM_TOKEN_TTL', 50),
        'vehicle_type' => env('FROTCOM_VEHICLE_TYPE', 'ZA OFFICE'),
        'last_gps_timezone' => env('FROTCOM_LAST_GPS_TZ', 'UTC'),
        'vehicle_map' => json_decode(env('FROTCOM_VEHICLE_MAP', '{}'), true) ?: [],
        'mock_mode' => env('FROTCOM_MOCK_MODE', false),
    ],

];
