<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_CUSTOMER = 'customer';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'total_spent',
        'points',
        'membership_level',
        'is_locked',
        'wallet_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_locked' => 'boolean',
            'total_spent' => 'decimal:2',
            'points' => 'integer',
            'wallet_balance' => 'decimal:2',
        ];
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class);
    }

    public function passwordResetTokens(): HasMany
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_STAFF,
        ], true);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function walletTopups(): HasMany
    {
        return $this->hasMany(WalletTopup::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function walletWithdrawals(): HasMany
    {
        return $this->hasMany(WalletWithdrawal::class);
    }

    public function warranties(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Warranty::class,
            \App\Models\Order::class,
            'user_id', // Foreign key on orders table
            'order_id', // Foreign key on warranties table
            'id', // Local key on users table
            'id' // Local key on orders table
        );
    }
}
