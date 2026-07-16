<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly BankTransactionLogService $logService,
    ) {}

    /**
     * Tạo yêu cầu hoàn tiền cho đơn hàng đã thanh toán bị hủy.
     *
     * @param  array{bank_name?: string, bank_account_number?: string, bank_account_name?: string}  $bankInfo
     */
    public function request(Order $order, User $user, string $method, array $bankInfo = []): RefundRequest
    {
        $payment = $order->payment;

        if (! $payment || $payment->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'refund' => 'Đơn hàng chưa thanh toán nên không cần hoàn tiền.',
            ]);
        }

        if ($order->refundRequest()->exists()) {
            throw ValidationException::withMessages([
                'refund' => 'Đơn hàng này đã có yêu cầu hoàn tiền.',
            ]);
        }

        if (! in_array($method, ['wallet', 'bank'], true)) {
            throw ValidationException::withMessages([
                'refund_method' => 'Phương thức hoàn tiền không hợp lệ.',
            ]);
        }

        if ($method === 'bank') {
            foreach (['bank_name', 'bank_account_number', 'bank_account_name'] as $field) {
                if (empty($bankInfo[$field])) {
                    throw ValidationException::withMessages([
                        $field => 'Vui lòng nhập đầy đủ thông tin tài khoản ngân hàng.',
                    ]);
                }
            }
        }

        return DB::transaction(function () use ($order, $user, $method, $bankInfo, $payment) {
            $amount = (float) $payment->amount;
            $now = now();

            $refund = RefundRequest::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'method' => $method,
                'amount' => $amount,
                'status' => $method === 'wallet' ? 'completed' : 'pending',
                'bank_name' => $bankInfo['bank_name'] ?? null,
                'bank_account_number' => $bankInfo['bank_account_number'] ?? null,
                'bank_account_name' => $bankInfo['bank_account_name'] ?? null,
                'requested_at' => $now,
                'eligible_at' => $method === 'bank'
                    ? $now->copy()->addDays(RefundRequest::MIN_BANK_PROCESSING_DAYS)
                    : null,
                'completed_at' => $method === 'wallet' ? $now : null,
            ]);

            if ($method === 'wallet') {
                $this->walletService->credit(
                    $user,
                    $amount,
                    'refund',
                    'Hoàn tiền đơn hàng #' . $order->order_code,
                    Order::class,
                    $order->id
                );
            }

            $payment->update(['payment_status' => 'refunded']);

            $this->logService->logRefund($refund, $refund->status, null, $method === 'wallet' ? 'Hoàn tự động vào ví.' : 'Khách tạo yêu cầu hoàn qua ngân hàng.');

            return $refund;
        });
    }

    /**
     * Admin xác nhận đã chuyển khoản hoàn tiền cho khách — bắt buộc đính kèm ảnh minh chứng đã
     * chuyển khoản thực tế (không xác nhận mù). Không còn ép chờ đủ thời gian xử lý tối thiểu:
     * admin có thể xác nhận ngay khi đã đủ căn cứ (ảnh bằng chứng hợp lệ).
     */
    public function completeBankRefund(RefundRequest $refund, User $admin, string $proofImagePath, ?string $adminNote = null): void
    {
        if ($refund->method !== 'bank') {
            throw ValidationException::withMessages(['refund' => 'Yêu cầu này không phải hoàn tiền qua ngân hàng.']);
        }

        if ($refund->status === 'completed') {
            return;
        }

        $refund->update([
            'status' => 'completed',
            'completed_at' => now(),
            'admin_note' => $adminNote,
            'proof_image' => $proofImagePath,
        ]);

        $this->logService->logRefund($refund->fresh(), 'completed', $admin, $adminNote);
    }

    public function markProcessing(RefundRequest $refund): void
    {
        if ($refund->status !== 'pending') {
            return;
        }

        $refund->update(['status' => 'processing']);
    }
}
