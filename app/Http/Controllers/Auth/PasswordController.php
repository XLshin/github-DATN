<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use App\Services\PasswordResetService;
use App\Support\CaptchaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    private const GENERIC_RESET_MESSAGE =
        'Nếu tài khoản tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi.';

    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {}

    /**
     * US05: Hiển thị form đổi mật khẩu
     */
    public function showChangePassword()
    {
        return view('client.profile.change-password');
    }

    /**
     * US05: Xử lý đổi mật khẩu
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', new StrongPassword],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Đổi mật khẩu thành công.');
    }

    /**
     * US04: Hiển thị form quên mật khẩu
     */
    public function showForgotPassword(Request $request)
    {
        $attempts = (int) session('password_reset_request_attempts', 0);
        $captcha = CaptchaHelper::isRequired($attempts)
            ? CaptchaHelper::generate()
            : ['required' => false, 'question' => null];

        return view('auth.forgot-password', compact('captcha'));
    }

    /**
     * US04: Gửi link đặt lại mật khẩu
     */
    public function sendResetLink(Request $request)
    {
        $attempts = (int) session('password_reset_request_attempts', 0);

        $rules = [
            'identifier' => ['required', 'string', 'max:255'],
        ];

        if (CaptchaHelper::isRequired($attempts)) {
            $rules['captcha'] = ['required', 'numeric'];
        }

        $request->validate($rules, [
            'identifier.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'captcha.required' => 'Vui lòng hoàn thành xác minh CAPTCHA.',
        ]);

        if (CaptchaHelper::isRequired($attempts) && ! CaptchaHelper::verify($request->input('captcha'))) {
            session()->increment('password_reset_request_attempts');

            throw ValidationException::withMessages([
                'captcha' => 'Xác minh CAPTCHA không chính xác.',
            ]);
        }

        $identifier = strtolower(trim($request->input('identifier')));
        $ip = $request->ip();

        if ($this->isRateLimited('password-reset-request:'.$ip, config('password_reset.rate_limit.request_per_ip_per_hour', 10))
            || $this->isRateLimited('password-reset-identifier:'.$identifier, config('password_reset.rate_limit.request_per_identifier_per_hour', 5))) {
            return back()->with('success', self::GENERIC_RESET_MESSAGE);
        }

        $throttleKey = 'password-reset-throttle:'.$identifier;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            return back()->with('success', self::GENERIC_RESET_MESSAGE);
        }

        RateLimiter::hit('password-reset-request:'.$ip, 3600);
        RateLimiter::hit('password-reset-identifier:'.$identifier, 3600);
        RateLimiter::hit($throttleKey, config('password_reset.rate_limit.throttle_seconds', 60));

        $this->passwordResetService->requestReset($identifier, $ip);

        CaptchaHelper::clear();
        session(['password_reset_request_attempts' => 0]);

        return back()->with('success', self::GENERIC_RESET_MESSAGE);
    }

    /**
     * US04: Hiển thị form đặt lại mật khẩu
     */
    public function showResetPassword(Request $request)
    {
        $token = $request->query('token', '');

        if ($token === '') {
            return redirect()
                ->route('password.request')
                ->with('error', 'Link đặt lại mật khẩu không hợp lệ.');
        }

        $record = $this->passwordResetService->validateToken($token);

        if (! $record) {
            session()->increment('password_reset_attempts');

            return redirect()
                ->route('password.request')
                ->with('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
        }

        $attempts = (int) session('password_reset_attempts', 0);
        $captcha = CaptchaHelper::isRequired($attempts)
            ? CaptchaHelper::generate()
            : ['required' => false, 'question' => null];

        return view('auth.reset-password', [
            'token' => $token,
            'captcha' => $captcha,
        ]);
    }

    /**
     * US04: Xử lý đặt lại mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $attempts = (int) session('password_reset_attempts', 0);

        $rules = [
            'token' => ['required', 'string', 'min:64'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ];

        if (CaptchaHelper::isRequired($attempts)) {
            $rules['captcha'] = ['required', 'numeric'];
        }

        $request->validate($rules, [
            'token.required' => 'Token đặt lại mật khẩu không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'captcha.required' => 'Vui lòng hoàn thành xác minh CAPTCHA.',
        ]);

        if (CaptchaHelper::isRequired($attempts) && ! CaptchaHelper::verify($request->input('captcha'))) {
            session()->increment('password_reset_attempts');

            throw ValidationException::withMessages([
                'captcha' => 'Xác minh CAPTCHA không chính xác.',
            ]);
        }

        $ip = $request->ip();
        $this->ensureNotRateLimited('password-reset-submit:'.$ip, config('password_reset.rate_limit.reset_attempts_per_ip_per_hour', 15));

        $success = $this->passwordResetService->resetPassword(
            $request->input('token'),
            $request->input('password'),
        );

        if (! $success) {
            session()->increment('password_reset_attempts');

            throw ValidationException::withMessages([
                'token' => 'Token đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
            ]);
        }

        CaptchaHelper::clear();
        session()->forget(['password_reset_attempts', 'password_reset_request_attempts']);

        return redirect()
            ->route('login')
            ->with('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập.');
    }

    private function isRateLimited(string $key, int $maxAttempts): bool
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return true;
        }

        return false;
    }

    private function ensureNotRateLimited(string $key, int $maxAttempts): void
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'identifier' => "Quá nhiều yêu cầu. Vui lòng thử lại sau {$seconds} giây.",
            ]);
        }

        RateLimiter::hit($key, 3600);
    }
}
