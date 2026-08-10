<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\User;
use App\Models\WalletWithdrawal;
<<<<<<< HEAD
use App\Notifications\WithdrawalCompletedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
=======
use Illuminate\Support\Facades\DB;
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletWithdrawalService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly BankTransactionLogService $logService,
<<<<<<< HEAD
        private readonly ReceiptImageService $receiptImageService,
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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

<<<<<<< HEAD
        if ($amount > (float) $user->wallet_balance) {
            throw ValidationException::withMessages([
                'amount' => 'Số tiền rút vượt quá số dư hiện có trong ví (' . number_format((float) $user->wallet_balance, 0, ',', '.') . ' đ).',
            ]);
        }

        return DB::transaction(function () use ($user, $bankAccount, $amount) {
            $now = now();

            // Rút tiền LUÔN cần admin tự tay chuyển khoản thật + đính kèm ảnh minh chứng
            // (WalletWithdrawalService::complete) — không tự động giả lập nữa dù dưới ngưỡng, vì
            // hệ thống không có API chuyển tiền ra ngân hàng thật (khác nạp ví/hoàn ví — tiền vào
            // có thể xác nhận thật qua webhook SePay, còn tiền ra luôn cần một người thao tác thật).
            $autoWithdraw = false;

=======
        return DB::transaction(function () use ($user, $bankAccount, $amount) {
            $now = now();

>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
<<<<<<< HEAD
                'simulate_confirm_at' => $autoWithdraw ? $now->copy()->addSeconds(random_int(8, 20)) : null,
            ]);

            // Mã đối soát cố định ngay từ lúc tạo — dùng làm nội dung chuyển khoản trong QR admin
            // quét để chuyển tiền ra (xem vietQrPayoutUrl()), SePay dò thấy mã này trong giao dịch
            // "tiền ra" của tài khoản cửa hàng thì tự xác nhận hoàn tất, không cần admin upload ảnh.
            $withdrawal->update(['transaction_code' => 'RUT' . str_pad((string) $withdrawal->id, 6, '0', STR_PAD_LEFT)]);

=======
            ]);

>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
            // Trừ ví ngay (giữ tiền); ném lỗi + rollback toàn bộ nếu không đủ số dư.
            $this->walletService->debit(
                $user,
                $amount,
                'withdrawal',
                'Yêu cầu rút tiền về ' . $bankAccount->bank_name . ' — ' . $this->maskAccountNumber($bankAccount->account_number),
                WalletWithdrawal::class,
                $withdrawal->id
            );

<<<<<<< HEAD
            $note = $autoWithdraw
                ? 'Khách tạo yêu cầu rút tiền, số tiền dưới ngưỡng — sẽ tự động xử lý.'
                : 'Khách tạo yêu cầu rút tiền, đã tạm giữ số dư.';
            $this->logService->logWithdrawal($withdrawal, 'pending', null, $note);
=======
            $this->logService->logWithdrawal($withdrawal, 'pending', null, 'Khách tạo yêu cầu rút tiền, đã tạm giữ số dư.');
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706

            return $withdrawal;
        });
    }

<<<<<<< HEAD
    /**
     * Mô phỏng ngân hàng báo đã chuyển tiền rút thành công — CHỈ chạy khi request() thực sự có đặt
     * simulate_confirm_at và đã đến hạn (hiện tại luôn null vì rút tiền bắt buộc admin xác nhận
     * thật, xem complete()). Giữ lại hàm này cho tương thích ngược, không tự ý bỏ qua điều kiện.
     */
    public function confirmSimulated(WalletWithdrawal $withdrawal): void
    {
        if (
            $withdrawal->status !== 'pending'
            || ! $withdrawal->simulate_confirm_at
            || $withdrawal->simulate_confirm_at->isFuture()
        ) {
            return;
        }

        $transactionCode = 'WD' . strtoupper(Str::random(10));
        $completedAt = now();

        $withdrawal->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'transaction_code' => $transactionCode,
            'admin_note' => 'Tự động xử lý (mô phỏng ngân hàng chuyển tiền xong).',
            // Tự động xử lý thì không có ảnh chuyển khoản thật; tự vẽ một ảnh hóa đơn làm chứng từ
            // lưu lại, giống vai trò của proof_image khi admin xác nhận thủ công.
            'proof_image' => $withdrawal->proof_image ?: $this->receiptImageService->generate(
                'RUT TIEN THANH CONG',
                number_format((float) $withdrawal->amount, 0, ',', '.') . ' VND',
                ['r' => 0, 'g' => 122, 'b' => 77],
                [
                    ['Don vi chuyen', config('services.sepay.account_name')],
                    ['Nguoi nhan', $withdrawal->account_holder_name],
                    ['Ngan hang nhan', $withdrawal->bank_name],
                    ['So TK nhan', $this->maskAccountNumber($withdrawal->account_number)],
                    ['Ma giao dich', $transactionCode],
                    ['Thoi gian', $completedAt->format('H:i:s d/m/Y')],
                ]
            ),
        ]);

        $this->logService->logWithdrawal($withdrawal->fresh(), 'completed', null, 'Tự động xác nhận — không cần admin (dưới ngưỡng ' . number_format(WalletWithdrawal::AUTO_WITHDRAWAL_MAX_AMOUNT, 0, ',', '.') . ' đ).');

        $this->sendWithdrawalNotifications($withdrawal->fresh());
    }

    /**
     * Xác nhận rút tiền THẬT từ webhook SePay (giao dịch "tiền ra" thật của tài khoản cửa hàng) —
     * admin chỉ cần quét QR (vietQrPayoutUrl trên WalletWithdrawal) bằng app ngân hàng để chuyển,
     * không cần upload ảnh minh chứng thủ công nữa vì SePay đã tự xác nhận tiền thật đã rời tài khoản.
     */
    public function confirmSepayPayout(WalletWithdrawal $withdrawal, ?string $referenceCode): void
    {
        if (! in_array($withdrawal->status, ['pending', 'processing'], true)) {
            return;
        }

        $completedAt = now();
        $transactionCode = $referenceCode ?: $withdrawal->transaction_code;

        $withdrawal->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'transaction_code' => $transactionCode,
            'admin_note' => 'Tự động xác nhận qua webhook SePay (đã quét QR chuyển khoản thật).',
            'proof_image' => $withdrawal->proof_image ?: $this->receiptImageService->generate(
                'RUT TIEN THANH CONG',
                number_format((float) $withdrawal->amount, 0, ',', '.') . ' VND',
                ['r' => 0, 'g' => 122, 'b' => 77],
                [
                    ['Don vi chuyen', config('services.sepay.account_name')],
                    ['Nguoi nhan', $withdrawal->account_holder_name],
                    ['Ngan hang nhan', $withdrawal->bank_name],
                    ['So TK nhan', $this->maskAccountNumber($withdrawal->account_number)],
                    ['Ma giao dich', (string) $transactionCode],
                    ['Thoi gian', $completedAt->format('H:i:s d/m/Y')],
                ]
            ),
        ]);

        $this->logService->logWithdrawal($withdrawal->fresh(), 'completed', null, 'Xác nhận thật qua webhook SePay — tiền đã rời tài khoản cửa hàng.');

        $this->sendWithdrawalNotifications($withdrawal->fresh());
    }

=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
<<<<<<< HEAD

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
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
<<<<<<< HEAD

            $withdrawal->user->notify(new \App\Notifications\WithdrawalRejectedNotification($withdrawal->fresh(), $reason));
=======
>>>>>>> 204f2abead4a1d35f4d5df9f5cb75a9805df8706
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
