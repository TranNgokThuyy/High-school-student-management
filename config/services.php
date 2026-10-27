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

    // Gửi SMS cho phụ huynh: driver 'log' (chỉ ghi log) hoặc 'webhook' (POST JSON {to, message})
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'webhook_url' => env('SMS_WEBHOOK_URL'),
        'token' => env('SMS_WEBHOOK_TOKEN'),
    ],

    // Zalo ZNS: access token của Zalo Official Account và ID mẫu tin đã được duyệt
    'zalo' => [
        'access_token' => env('ZALO_ZNS_ACCESS_TOKEN'),
        'template_id' => env('ZALO_ZNS_TEMPLATE_ID'),
        // Tự làm mới token: ID và khóa bí mật của ứng dụng trên developers.zalo.me, refresh token ban đầu
        'app_id' => env('ZALO_APP_ID'),
        'app_secret' => env('ZALO_APP_SECRET'),
        'refresh_token' => env('ZALO_OA_REFRESH_TOKEN'),
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
