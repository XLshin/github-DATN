<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || strlen($value) < 8) {
            $fail('Mật khẩu phải có ít nhất 8 ký tự.');

            return;
        }

        if (! preg_match('/[a-z]/', $value)) {
            $fail('Mật khẩu phải chứa ít nhất một chữ thường.');

            return;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $fail('Mật khẩu phải chứa ít nhất một chữ hoa.');

            return;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('Mật khẩu phải chứa ít nhất một chữ số.');

            return;
        }

        if (! preg_match('/[^a-zA-Z0-9]/', $value)) {
            $fail('Mật khẩu phải chứa ít nhất một ký tự đặc biệt.');
        }
    }
}
