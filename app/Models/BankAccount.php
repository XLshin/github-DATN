<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'is_verified',
        'is_default',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_default' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Chuẩn hóa chuỗi để so khớp tên (bỏ dấu, khoảng trắng thừa, không phân biệt hoa/thường)
     * — dùng để tự động xác minh tên chủ TK có khớp tên tài khoản đăng ký hay không.
     */
    public static function normalizeName(string $name): string
    {
        $name = trim(mb_strtolower($name));
        $name = preg_replace('/\s+/', ' ', $name);

        $unicode = [
            'à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ' => 'a',
            'è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ' => 'e',
            'ì|í|ị|ỉ|ĩ' => 'i',
            'ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ' => 'o',
            'ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ' => 'u',
            'ỳ|ý|ỵ|ỷ|ỹ' => 'y',
            'đ' => 'd',
        ];

        foreach ($unicode as $pattern => $replacement) {
            $name = preg_replace('/(' . $pattern . ')/u', $replacement, $name);
        }

        return $name;
    }

    public static function namesMatch(string $a, string $b): bool
    {
        return static::normalizeName($a) === static::normalizeName($b);
    }
}
