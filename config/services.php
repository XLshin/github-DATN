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

    // MoMo — cổng thanh toán thật, môi trường Test/Sandbox (captureWallet).
    'momo' => [
        // Tắt mặc định: bộ Partner Code test công khai tạo được giao dịch thật trên
        // test-payment.momo.vn nhưng không có app/tài khoản test công khai đi kèm để hoàn tất
        // (cần đăng ký M4B riêng). Bật lên khi đã có tài khoản sandbox riêng để test end-to-end.
        'enabled' => env('MOMO_ENABLED', false),
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key' => env('MOMO_ACCESS_KEY'),
        'secret_key' => env('MOMO_SECRET_KEY'),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'return_url' => env('MOMO_RETURN_URL'),
        'ipn_url' => env('MOMO_IPN_URL'),
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
