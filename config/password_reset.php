<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token expiry (minutes)
    |--------------------------------------------------------------------------
    */
    'expire_minutes' => (int) env('PASSWORD_RESET_EXPIRE', 20),

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'request_per_ip_per_hour' => (int) env('PASSWORD_RESET_IP_LIMIT', 10),
        'request_per_identifier_per_hour' => (int) env('PASSWORD_RESET_IDENTIFIER_LIMIT', 5),
        'reset_attempts_per_ip_per_hour' => (int) env('PASSWORD_RESET_ATTEMPT_LIMIT', 15),
        'throttle_seconds' => (int) env('PASSWORD_RESET_THROTTLE', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA after N failed attempts (per session)
    |--------------------------------------------------------------------------
    */
    'captcha_after_attempts' => (int) env('PASSWORD_RESET_CAPTCHA_AFTER', 3),

    /*
    |--------------------------------------------------------------------------
    | Base URL for reset links (never use Host header)
    |--------------------------------------------------------------------------
    */
    'app_url' => env('APP_URL', 'http://localhost'),

];
