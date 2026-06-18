<?php

namespace App\Support;

class CaptchaHelper
{
    public static function generate(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        session([
            'password_reset_captcha_answer' => $a + $b,
        ]);

        return [
            'question' => "{$a} + {$b} = ?",
            'required' => true,
        ];
    }

    public static function isRequired(int $attempts): bool
    {
        return $attempts >= config('password_reset.captcha_after_attempts', 3);
    }

    public static function verify(?string $answer): bool
    {
        $expected = session('password_reset_captcha_answer');

        if ($expected === null) {
            return false;
        }

        return (int) $answer === (int) $expected;
    }

    public static function clear(): void
    {
        session()->forget('password_reset_captcha_answer');
    }
}
