<?php

return [

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_VISION_MODEL', 'gemini-3.1-flash-lite'),
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
        'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v23.0'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '92'),
        'test_template' => env('WHATSAPP_TEST_TEMPLATE'),
        'test_template_language' => env('WHATSAPP_TEST_TEMPLATE_LANGUAGE', 'en_US'),
        'notification_template' => env('WHATSAPP_NOTIFICATION_TEMPLATE'),
        'notification_template_language' => env('WHATSAPP_NOTIFICATION_TEMPLATE_LANGUAGE', 'en_US'),
    ],

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

];
