<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService
{
  private const TOKEN_BYTES = 32;

  public function findUserByIdentifier(string $identifier): ?User
  {
    $identifier = trim($identifier);

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
      return User::query()
        ->where('email', strtolower($identifier))
        ->first();
    }

    $normalizedPhone = $this->normalizePhone($identifier);

    return User::query()
      ->where(function ($query) use ($normalizedPhone, $identifier) {
        $query->where('phone', $normalizedPhone)
          ->orWhere('phone', $identifier);
      })
      ->first();
  }

  public function requestReset(string $identifier, ?string $ipAddress = null): void
  {
    $user = $this->findUserByIdentifier($identifier);

    if (! $user || empty($user->email)) {
      return;
    }

    $this->invalidateUserTokens($user);

    $plainToken = $this->generatePlainToken();
    $tokenHash = $this->hashToken($plainToken);

    PasswordResetToken::query()->create([
      'user_id' => $user->id,
      'token_hash' => $tokenHash,
      'expires_at' => now()->addMinutes(config('password_reset.expire_minutes', 20)),
      'ip_address' => $ipAddress,
    ]);

    $resetUrl = $this->buildResetUrl($plainToken);

    $user->notify(new ResetPasswordNotification($resetUrl));
  }

  public function validateToken(string $plainToken): ?PasswordResetToken
  {
    if (strlen($plainToken) < 32) {
      return null;
    }

    $tokenHash = $this->hashToken($plainToken);

    $record = PasswordResetToken::query()
      ->where('token_hash', $tokenHash)
      ->whereNull('used_at')
      ->where('expires_at', '>', now())
      ->first();

    return $record?->isValid() ? $record : null;
  }

  public function resetPassword(string $plainToken, string $newPassword): bool
  {
    $record = $this->validateToken($plainToken);

    if (! $record) {
      return false;
    }

    $user = $record->user;

    if (! $user) {
      return false;
    }

    DB::transaction(function () use ($user, $record, $newPassword) {
      $user->forceFill([
        'password' => $newPassword,
        'remember_token' => Str::random(60),
      ])->save();

      $record->markAsUsed();
      $this->invalidateUserTokens($user, exceptId: $record->id);

      $this->logoutAllSessions($user);

      $user->notify(new PasswordChangedNotification());
    });

    return true;
  }

  public function buildResetUrl(string $plainToken): string
  {
    $baseUrl = rtrim(config('password_reset.app_url'), '/');

    return $baseUrl.'/reset-password?token='.urlencode($plainToken);
  }

  private function generatePlainToken(): string
  {
    return bin2hex(random_bytes(self::TOKEN_BYTES));
  }

  private function hashToken(string $plainToken): string
  {
    return hash_hmac('sha256', $plainToken, (string) config('app.key'));
  }

  private function normalizePhone(string $phone): string
  {
    return preg_replace('/[^0-9+]/', '', $phone) ?? $phone;
  }

  private function invalidateUserTokens(User $user, ?int $exceptId = null): void
  {
    $query = PasswordResetToken::query()
      ->where('user_id', $user->id)
      ->whereNull('used_at');

    if ($exceptId !== null) {
      $query->where('id', '!=', $exceptId);
    }

    $query->update(['used_at' => now()]);
  }

  private function logoutAllSessions(User $user): void
  {
    DB::table('sessions')
      ->where('user_id', $user->id)
      ->delete();
  }
}
