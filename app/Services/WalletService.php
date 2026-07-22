<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /** Phương thức nạp qua cổng có phiên giao dịch giới hạn thời gian (giống thực tế QR/thẻ hết hạn). */
    private const EXPIRING_METHODS = ['card', 'momo', 'vnpay'];

    /** Phương thức online được tự động xác nhận (mô phỏng), không cần đối soát thủ công. */
    public const AUTO_CONFIRM_METHODS = ['bank_transfer', 'card', 'momo', 'vnpay'];

    private const TOPUP_EXPIRY_MINUTES = 15;

    public function __construct(
        private readonly BankTransactionLogService $logService,
    ) {}

    /**
     * Cộng tiền vào ví, có khóa hàng để tránh race-condition khi nhiều giao dịch cùng lúc.
     */
    public function credit(User $user, float $amount, string $type, string $description, ?string $referenceType = null, ?int $referenceId = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Số tiền không hợp lệ.']);
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceType, $referenceId) {
            $freshUser = User::query()->whereKey($user->id)->lockForUpdate()->first();
            $newBalance = (float) $freshUser->wallet_balance + $amount;

            $freshUser->update(['wallet_balance' => $newBalance]);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Trừ tiền trong ví; ném lỗi nếu số dư không đủ.
     */
    public function debit(User $user, float $amount, string $type, string $description, ?string $referenceType = null, ?int $referenceId = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Số tiền không hợp lệ.']);
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceType, $referenceId) {
            $freshUser = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ((float) $freshUser->wallet_balance < $amount) {
                throw ValidationException::withMessages(['wallet' => 'Số dư ví không đủ để thanh toán.']);
            }

            $newBalance = (float) $freshUser->wallet_balance - $amount;
            $freshUser->update(['wallet_balance' => $newBalance]);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    public function initiateTopup(User $user, float $amount, string $paymentMethod): WalletTopup
    {
        if ($amount < 10000) {
            throw ValidationException::withMessages(['amount' => 'Số tiền nạp tối thiểu là 10.000 đ.']);
        }

        $topup = WalletTopup::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'expires_at' => in_array($paymentMethod, self::EXPIRING_METHODS, true)
                ? now()->addMinutes(self::TOPUP_EXPIRY_MINUTES)
                : null,
            // Mô phỏng cổng thanh toán/ngân hàng báo có tiền sau một khoảng trễ ngẫu nhiên, giống
            // cảm giác chờ đối soát thật (đồ án — không gọi cổng thật). Áp dụng cho mọi phương thức
            // online (bank_transfer/momo/vnpay/card), không chỉ chuyển khoản.
            'simulate_confirm_at' => in_array($paymentMethod, self::AUTO_CONFIRM_METHODS, true)
                ? now()->addSeconds(random_int(8, 20))
                : null,
        ]);

        $this->logService->logTopup($topup, 'pending', null, 'Khách tạo yêu cầu nạp ví.');

        return $topup;
    }

    /**
     * Admin xác nhận đã nhận được tiền nạp ví (áp dụng cho mọi phương thức) — bắt buộc nhập đúng
     * số tiền thực nhận để tránh duyệt nhầm, mọi lượt duyệt đều ghi nhận admin xử lý.
     *
     * @throws ValidationException nếu yêu cầu không còn ở trạng thái chờ, hoặc số tiền nhập không khớp
     */
    public function confirmTopupByAdmin(WalletTopup $topup, User $admin, float $confirmedAmount, ?string $adminNote = null): void
    {
        if ($topup->payment_status !== 'pending') {
            throw ValidationException::withMessages([
                'topup' => 'Yêu cầu nạp ví không ở trạng thái chờ xác nhận.',
            ]);
        }

        if (abs($confirmedAmount - (float) $topup->amount) > 0.01) {
            throw ValidationException::withMessages([
                'confirmed_amount' => 'Số tiền xác nhận (' . number_format($confirmedAmount, 0, ',', '.')
                    . ' đ) không khớp với số tiền yêu cầu (' . number_format((float) $topup->amount, 0, ',', '.')
                    . ' đ). Vui lòng kiểm tra lại sao kê hoặc từ chối yêu cầu này.',
            ]);
        }

        $this->markPaid($topup, $admin->id, $adminNote);
    }

    /**
     * Mô phỏng ngân hàng báo có tiền: dùng cho demo đồ án, được gọi khi đã quá thời điểm
     * simulate_confirm_at của topup (xem WalletController::topupStatus). Cộng tiền và đóng
     * yêu cầu y hệt như admin xác nhận thủ công, chỉ khác là không có admin thực hiện.
     */
    public function confirmSimulated(WalletTopup $topup): void
    {
        if ($topup->payment_status !== 'pending') {
            return;
        }

        $this->markPaid($topup, null, 'Tự động xác nhận (mô phỏng ngân hàng báo có).');
    }

    private function markPaid(WalletTopup $topup, ?int $adminId, ?string $note): void
    {
        DB::transaction(function () use ($topup, $adminId, $note) {
            $topup->update([
                'payment_status' => 'paid',
                'transaction_code' => strtoupper($topup->payment_method) . strtoupper(Str::random(10)),
                'paid_at' => now(),
                'confirmed_by' => $adminId,
                'admin_note' => $note,
            ]);

            $this->credit(
                $topup->user,
                (float) $topup->amount,
                'topup',
                'Nạp tiền vào ví qua ' . $this->methodLabel($topup->payment_method),
                WalletTopup::class,
                $topup->id
            );

            $admin = $adminId ? User::find($adminId) : null;
            $this->logService->logTopup($topup->fresh(), 'paid', $admin, $note);

            $this->sendTopupNotifications($topup->fresh());
        });
    }

    /**
     * Mô phỏng gửi email + SMS báo khách đã nạp ví thành công — không gọi nhà cung cấp thật
     * (đồ án), chỉ ghi log có nội dung đầy đủ, giống hệt cách RefundService/WalletWithdrawalService
     * báo hoàn tiền/rút tiền. Áp dụng cho cả nạp tự động lẫn admin xác nhận thủ công.
     */
    private function sendTopupNotifications(WalletTopup $topup): void
    {
        $topup->loadMissing('user');
        $user = $topup->user;
        $amountText = number_format((float) $topup->amount, 0, ',', '.') . ' đ';
        $methodText = $this->methodLabel($topup->payment_method);

        Log::info('[MÔ PHỎNG EMAIL] Gửi email báo nạp ví thành công', [
            'to' => $user->email ?? 'unknown',
            'subject' => 'ByteZone đã cộng tiền vào ví của bạn',
            'body' => "Chào {$user->name}, chúng tôi đã cộng {$amountText} vào ví qua {$methodText}. Cảm ơn bạn đã sử dụng ByteZone.",
        ]);

        Log::info('[MÔ PHỎNG SMS] Gửi SMS báo nạp ví thành công', [
            'to' => $user->phone ?? 'unknown',
            'body' => "ByteZone: Da cong {$amountText} vao vi qua {$methodText}.",
        ]);

        $user->notify(new \App\Notifications\WalletCreditedNotification(
            (float) $topup->amount,
            'Nạp tiền qua ' . $methodText . '.'
        ));
    }

    /**
     * Admin từ chối yêu cầu nạp ví (không nhận được tiền / sai số tiền / sai nội dung...).
     */
    public function rejectTopup(WalletTopup $topup, User $admin, string $reason): void
    {
        if ($topup->payment_status !== 'pending') {
            throw ValidationException::withMessages([
                'topup' => 'Yêu cầu nạp ví không ở trạng thái chờ xử lý.',
            ]);
        }

        $topup->update([
            'payment_status' => 'failed',
            'rejected_by' => $admin->id,
            'reject_reason' => $reason,
        ]);

        $this->logService->logTopup($topup->fresh(), 'failed', $admin, 'Từ chối: ' . $reason);
    }

    public function expireTopup(WalletTopup $topup): void
    {
        if (! $topup->isExpired()) {
            return;
        }

        $topup->update([
            'payment_status' => 'failed',
            'payer_note' => 'Giao dịch hết hạn do quá thời gian thanh toán.',
        ]);
    }

    private function methodLabel(string $method): string
    {
        return match ($method) {
            'bank_transfer' => 'chuyển khoản ngân hàng',
            'momo' => 'Ví MoMo',
            'vnpay' => 'VNPAY',
            'card' => 'thẻ',
            default => $method,
        };
    }
}
