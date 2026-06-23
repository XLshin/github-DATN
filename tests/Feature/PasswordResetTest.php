<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use App\Services\PasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_returns_generic_message_for_unknown_identifier(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'identifier' => 'unknown@example.com',
        ]);

        $response->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_forgot_password_sends_email_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'identifier' => 'user@example.com',
        ]);

        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_same_message_for_existing_and_non_existing(): void
    {
        Notification::fake();

        User::factory()->create(['email' => 'exists@example.com']);

        $existing = $this->post(route('password.email'), [
            'identifier' => 'exists@example.com',
        ])->getSession()->get('success');

        $missing = $this->post(route('password.email'), [
            'identifier' => 'missing@example.com',
        ])->getSession()->get('success');

        $this->assertSame($existing, $missing);
    }

    public function test_reset_password_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'OldPass1!',
        ]);

        $service = app(PasswordResetService::class);
        $service->requestReset('reset@example.com');

        $tokenRecord = DB::table('password_reset_tokens')->where('user_id', $user->id)->first();
        $this->assertNotNull($tokenRecord);

        $plainToken = $this->extractTokenFromNotification($user);

        $response = $this->post(route('password.update'), [
            'token' => $plainToken,
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertRedirect(route('login'));
        $user->refresh();
        $this->assertTrue(Hash::check('NewPass1!', $user->password));
        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_reset_token_is_one_time_use(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'once@example.com']);

        $service = app(PasswordResetService::class);
        $service->requestReset('once@example.com');
        $plainToken = $this->extractTokenFromNotification($user);

        $this->post(route('password.update'), [
            'token' => $plainToken,
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ])->assertRedirect(route('login'));

        $this->post(route('password.update'), [
            'token' => $plainToken,
            'password' => 'Another1!',
            'password_confirmation' => 'Another1!',
        ])->assertSessionHasErrors('token');
    }

    public function test_invalid_reset_token_redirects(): void
    {
        $response = $this->get(route('password.reset', ['token' => str_repeat('a', 64)]));

        $response->assertRedirect(route('password.request'));
    }

    private function extractTokenFromNotification(User $user): string
    {
        $notifications = Notification::sent($user, ResetPasswordNotification::class);
        $notification = $notifications->first();
        $mail = $notification->toMail($user);
        $url = $mail->actionUrl;

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return $query['token'];
    }
}
