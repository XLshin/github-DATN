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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    // SePay (hoặc Casso) — dịch vụ đọc biến động số dư ngân hàng qua webhook để
    // tự động xác nhận thanh toán chuyển khoản, không cần admin đối soát thủ công.
    'sepay' => [
        'webhook_token' => env('SEPAY_WEBHOOK_TOKEN'),
        'bank_id' => env('SEPAY_BANK_ID', 'VCB'),
        'account_number' => env('SEPAY_ACCOUNT_NUMBER', '1234567890'),
        'account_name' => env('SEPAY_ACCOUNT_NAME', 'BYTE ZONE STORE'),
    ],

];
