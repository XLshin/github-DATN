<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\User;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\DB;
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

        return DB::transaction(function () use ($user, $bankAccount, $amount) {
            $now = now();

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

            $this->logService->logWithdrawal($withdrawal, 'pending', null, 'Khách tạo yêu cầu rút tiền, đã tạm giữ số dư.');

            return $withdrawal;
        });
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
