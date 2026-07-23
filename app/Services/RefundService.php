<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;
use App\Notifications\RefundCompletedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            // Đơn tự hủy trước khi giao (điều kiện bắt buộc để vào được luồng này — xem
            // OrderController::cancel) + số tiền dưới ngưỡng thì tự động hoàn, không cần admin.
            // Trên ngưỡng vẫn giữ quy trình cũ: admin xác nhận thủ công kèm ảnh minh chứng.
            $autoRefund = $method === 'bank' && $amount <= RefundRequest::AUTO_REFUND_MAX_AMOUNT;

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
                // Mô phỏng ngân hàng xử lý xong lệnh chuyển tiền sau một khoảng trễ ngẫu nhiên,
                // giống cảm giác chờ xử lý thật (đồ án — không gọi ngân hàng thật).
                'simulate_confirm_at' => $autoRefund ? $now->copy()->addSeconds(random_int(8, 20)) : null,
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

            $note = match (true) {
                $method === 'wallet' => 'Hoàn tự động vào ví.',
                $autoRefund => 'Đơn hủy trước khi giao, số tiền dưới ngưỡng — tự động hoàn qua ngân hàng.',
                default => 'Khách tạo yêu cầu hoàn qua ngân hàng, chờ admin đối soát.',
            };
            $this->logService->logRefund($refund, $refund->status, null, $note);

            // Ví hoàn ngay lập tức thì báo cho khách ngay; hoàn ngân hàng thủ công (chờ admin) thì
            // chỉ báo khi thực sự có tiền về (completeBankRefund / confirmSimulatedBankRefund).
            if ($refund->status === 'completed') {
                $this->sendRefundNotifications($refund);
            }

            return $refund;
        });
    }

    /**
     * Mô phỏng gửi email + SMS báo khách đã nhận được tiền hoàn — không gọi nhà cung cấp thật
     * (đồ án), chỉ ghi log có nội dung đầy đủ và đánh dấu notified_at để hiển thị trên trang chi
     * tiết đơn hàng, mô phỏng đúng trải nghiệm nhận được thông báo hoàn tiền ngoài đời thực.
     */
    private function sendRefundNotifications(RefundRequest $refund): void
    {
        $refund->loadMissing(['user', 'order']);
        $user = $refund->user;
        $order = $refund->order;
        $amountText = number_format((float) $refund->amount, 0, ',', '.') . ' đ';
        $methodText = $refund->method === 'wallet' ? 'Ví ByteZone' : 'tài khoản ngân hàng ' . ($refund->bank_name ?? '');

        Log::info('[MÔ PHỎNG EMAIL] Gửi email báo hoàn tiền', [
            'to' => $user->email ?? 'unknown',
            'subject' => 'ByteZone đã hoàn tiền cho đơn #' . $order->order_code,
            'body' => "Chào {$user->name}, chúng tôi đã hoàn {$amountText} cho đơn hàng #{$order->order_code} vào {$methodText}. Cảm ơn bạn đã mua sắm tại ByteZone.",
        ]);

        Log::info('[MÔ PHỎNG SMS] Gửi SMS báo hoàn tiền', [
            'to' => $user->phone ?? 'unknown',
            'body' => "ByteZone: Da hoan {$amountText} cho don #{$order->order_code} vao {$methodText}.",
        ]);

        $user->notify(new RefundCompletedNotification($refund));

        $refund->update(['notified_at' => now()]);
    }

    /**
     * Mô phỏng ngân hàng báo đã chuyển tiền hoàn thành công: dùng cho demo đồ án, được gọi khi
     * đã quá thời điểm simulate_confirm_at (xem OrderController::statusCheck). Chỉ áp dụng cho
     * yêu cầu dưới ngưỡng tự động — yêu cầu vượt ngưỡng luôn cần admin xác nhận thủ công.
     */
    public function confirmSimulatedBankRefund(RefundRequest $refund): void
    {
        if (
            $refund->method !== 'bank'
            || $refund->status !== 'pending'
            || ! $refund->simulate_confirm_at
            || $refund->simulate_confirm_at->isFuture()
        ) {
            return;
        }

        $refund->update([
            'status' => 'completed',
            'completed_at' => now(),
            'admin_note' => 'Tự động hoàn tiền (mô phỏng ngân hàng xử lý xong).',
        ]);

        $this->logService->logRefund($refund->fresh(), 'completed', null, 'Tự động xác nhận — không cần admin (dưới ngưỡng ' . number_format(RefundRequest::AUTO_REFUND_MAX_AMOUNT, 0, ',', '.') . ' đ).');

        $this->sendRefundNotifications($refund->fresh());
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

        $this->sendRefundNotifications($refund->fresh());
    }

    public function markProcessing(RefundRequest $refund): void
    {
        if ($refund->status !== 'pending') {
            return;
        }

        $refund->update(['status' => 'processing']);
    }
}
