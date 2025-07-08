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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
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

    'opencage' => [
        'api_key' => env('OPENCAGE_API_KEY'),
        // language: we default to 'en'
        'language' => env('OPENCAGE_LANGUAGE', 'en'),
        // pretty: OpenCage default is not set (false), we set to 1 for easier debugging
        'pretty' => env('OPENCAGE_PRETTY', 1),
        // no_record: OpenCage default is not set (false), we set to 1 for privacy
        'no_record' => env('OPENCAGE_NO_RECORD', 1),
    ],

];
