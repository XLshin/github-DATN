<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\User;
use App\Models\WalletWithdrawal;
use App\Notifications\WithdrawalCompletedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletWithdrawalService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly BankTransactionLogService $logService,
    ) {}

    /**
     * Tạo yêu cầu rút tiền về ngân hàng. Trừ ví ngay lập tức (giữ tiền) để tránh khách vừa rút
     * vừa tiêu cùng lúc số dư đó — nếu bị từ chối, tiền sẽ được hoàn lại ví.
     *
     * @throws ValidationException nếu tài khoản ngân hàng chưa xác minh, số dư không đủ, hoặc số tiền dưới mức tối thiểu
     */
    public function request(User $user, BankAccount $bankAccount, float $amount): WalletWithdrawal
    {
        if ((int) $bankAccount->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'bank_account_id' => 'Tài khoản ngân hàng không hợp lệ.',
            ]);
        }

        if (! $bankAccount->is_verified) {
            throw ValidationException::withMessages([
                'bank_account_id' => 'Tài khoản ngân hàng này chưa được xác minh. Vui lòng liên kết tài khoản đứng tên bạn hoặc chờ admin xác minh trước khi rút tiền.',
            ]);
        }

        if ($amount < WalletWithdrawal::MIN_AMOUNT) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền rút tối thiểu là ' . number_format(WalletWithdrawal::MIN_AMOUNT, 0, ',', '.') . ' đ.',
            ]);
        }

        if ($amount > (float) $user->wallet_balance) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền rút vượt quá số dư hiện có trong ví (' . number_format((float) $user->wallet_balance, 0, ',', '.') . ' đ).',
            ]);
        }

        return DB::transaction(function () use ($user, $bankAccount, $amount) {
            $now = now();

            // Rút dưới ngưỡng thì tự động xử lý (mô phỏng ngân hàng chuyển xong sau khoảng trễ
            // ngắn), giống hệt cơ chế đã dùng cho hoàn tiền/nạp ví — không cần chờ admin duyệt.
            // Trên ngưỡng vẫn giữ quy trình cũ: admin xác nhận thủ công kèm ảnh minh chứng.
            $autoWithdraw = $amount <= WalletWithdrawal::AUTO_WITHDRAWAL_MAX_AMOUNT;

            $withdrawal = WalletWithdrawal::create([
                'user_id' => $user->id,
                'bank_account_id' => $bankAccount->id,
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_holder_name' => $bankAccount->account_holder_name,
                'amount' => $amount,
                'status' => 'pending',
                'requested_at' => $now,
                'eligible_at' => $now->copy()->addDays(WalletWithdrawal::MIN_PROCESSING_DAYS),
                'simulate_confirm_at' => $autoWithdraw ? $now->copy()->addSeconds(random_int(8, 20)) : null,
            ]);

            // Trừ ví ngay (giữ tiền); ném lỗi + rollback toàn bộ nếu không đủ số dư.
            $this->walletService->debit(
                $user,
                $amount,
                'withdrawal',
                'Yêu cầu rút tiền về ' . $bankAccount->bank_name . ' — ' . $this->maskAccountNumber($bankAccount->account_number),
                WalletWithdrawal::class,
                $withdrawal->id
            );

            $note = $autoWithdraw
                ? 'Khách tạo yêu cầu rút tiền, số tiền dưới ngưỡng — sẽ tự động xử lý.'
                : 'Khách tạo yêu cầu rút tiền, đã tạm giữ số dư.';
            $this->logService->logWithdrawal($withdrawal, 'pending', null, $note);

            return $withdrawal;
        });
    }

    /**
     * Mô phỏng ngân hàng báo đã chuyển tiền rút thành công: dùng cho demo đồ án, được gọi khi đã
     * quá thời điểm simulate_confirm_at. Chỉ áp dụng cho yêu cầu dưới ngưỡng tự động — yêu cầu
     * vượt ngưỡng luôn cần admin xác nhận thủ công (xem complete()).
     */
    public function confirmSimulated(WalletWithdrawal $withdrawal): void
    {
        if ($withdrawal->status !== 'pending') {
            return;
        }

        $withdrawal->update([
            'status' => 'completed',
            'completed_at' => now(),
            'transaction_code' => 'WD' . strtoupper(Str::random(10)),
            'admin_note' => 'Tự động xử lý (mô phỏng ngân hàng chuyển tiền xong).',
        ]);

        $this->logService->logWithdrawal($withdrawal->fresh(), 'completed', null, 'Tự động xác nhận — không cần admin (dưới ngưỡng ' . number_format(WalletWithdrawal::AUTO_WITHDRAWAL_MAX_AMOUNT, 0, ',', '.') . ' đ).');

        $this->sendWithdrawalNotifications($withdrawal->fresh());
    }

    public function markProcessing(WalletWithdrawal $withdrawal): void
    {
        if ($withdrawal->status !== 'pending') {
            return;
        }

        $withdrawal->update(['status' => 'processing']);
    }

    /**
     * Admin xác nhận đã chuyển khoản cho khách — bắt buộc đính kèm ảnh minh chứng đã chuyển khoản
     * thực tế (không xác nhận mù). Không còn ép chờ đủ thời gian xử lý tối thiểu: admin có thể
     * xác nhận ngay khi đã đủ căn cứ (ảnh bằng chứng hợp lệ).
     */
    public function complete(WalletWithdrawal $withdrawal, User $admin, string $proofImagePath, ?string $adminNote = null): void
    {
        if (! in_array($withdrawal->status, ['pending', 'processing'], true)) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Yêu cầu rút tiền không ở trạng thái có thể hoàn tất.',
            ]);
        }

        $withdrawal->update([
            'status' => 'completed',
            'completed_at' => now(),
            'transaction_code' => 'WD' . strtoupper(Str::random(10)),
            'confirmed_by' => $admin->id,
            'admin_note' => $adminNote,
            'proof_image' => $proofImagePath,
        ]);

        $this->logService->logWithdrawal($withdrawal->fresh(), 'completed', $admin, $adminNote);

        $this->sendWithdrawalNotifications($withdrawal->fresh());
    }

    /**
     * Mô phỏng gửi email + SMS báo khách đã nhận được tiền rút — không gọi nhà cung cấp thật
     * chỉ ghi log có nội dung đầy đủ, giống hệt cách RefundService báo hoàn tiền. Áp
     * dụng cho cả rút tự động lẫn admin xác nhận thủ công.
     */
    private function sendWithdrawalNotifications(WalletWithdrawal $withdrawal): void
    {
        $withdrawal->loadMissing('user');
        $user = $withdrawal->user;
        $amountText = number_format((float) $withdrawal->amount, 0, ',', '.') . ' đ';
        $accountText = $withdrawal->bank_name . ' — ' . $this->maskAccountNumber($withdrawal->account_number);

        Log::info('[MÔ PHỎNG EMAIL] Gửi email báo rút tiền thành công', [
            'to' => $user->email ?? 'unknown',
            'subject' => 'ByteZone đã chuyển tiền rút ví của bạn',
            'body' => "Chào {$user->name}, chúng tôi đã chuyển {$amountText} về tài khoản {$accountText}. Cảm ơn bạn đã sử dụng ByteZone.",
        ]);

        Log::info('[MÔ PHỎNG SMS] Gửi SMS báo rút tiền thành công', [
            'to' => $user->phone ?? 'unknown',
            'body' => "ByteZone: Da chuyen {$amountText} ve tai khoan {$accountText}.",
        ]);

        $user->notify(new WithdrawalCompletedNotification($withdrawal));
    }

    /**
     * Admin từ chối yêu cầu rút tiền — hoàn lại số dư đã tạm giữ vào ví khách hàng.
     */
    public function reject(WalletWithdrawal $withdrawal, User $admin, string $reason): void
    {
        if (! in_array($withdrawal->status, ['pending', 'processing'], true)) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Yêu cầu rút tiền không ở trạng thái có thể từ chối.',
            ]);
        }

        DB::transaction(function () use ($withdrawal, $admin, $reason) {
            $withdrawal->update([
                'status' => 'rejected',
                'rejected_by' => $admin->id,
                'reject_reason' => $reason,
            ]);

            $this->walletService->credit(
                $withdrawal->user,
                (float) $withdrawal->amount,
                'withdrawal',
                'Hoàn lại số dư do yêu cầu rút tiền #' . $withdrawal->id . ' bị từ chối: ' . $reason,
                WalletWithdrawal::class,
                $withdrawal->id
            );

            $this->logService->logWithdrawal($withdrawal->fresh(), 'rejected', $admin, $reason);
        });
    }

    private function maskAccountNumber(string $accountNumber): string
    {
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return $accountNumber;
        }

        return str_repeat('*', $length - 4) . substr($accountNumber, -4);
    }
}
