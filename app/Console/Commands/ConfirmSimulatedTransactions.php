<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\WalletTopup;
use App\Models\WalletWithdrawal;
use App\Services\CheckoutService;
use App\Services\PaymentWebhookService;
use App\Services\RefundService;
use App\Services\WalletService;
use App\Services\WalletWithdrawalService;
use Illuminate\Console\Command;

class ConfirmSimulatedTransactions extends Command
{
    protected $signature = 'transactions:confirm-simulated';

    protected $description = 'Tự động xác nhận các giao dịch mô phỏng (thanh toán, nạp ví, hoàn tiền, rút tiền) đã đến hạn simulate_confirm_at, không phụ thuộc việc có ai đang mở đúng trang để kích hoạt hay không.';

    public function handle(
        PaymentWebhookService $paymentWebhookService,
        WalletService $walletService,
        RefundService $refundService,
        WalletWithdrawalService $withdrawalService,
    ): int {
        Payment::query()
            ->where('payment_status', 'pending')
            ->whereIn('payment_method', CheckoutService::AUTO_CONFIRM_METHODS)
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->each(function (Payment $payment) use ($paymentWebhookService) {
                if (! $payment->isExpired()) {
                    $paymentWebhookService->confirmSimulatedBankTransfer($payment);
                }
            });

        WalletTopup::query()
            ->where('payment_status', 'pending')
            ->whereIn('payment_method', WalletService::AUTO_CONFIRM_METHODS)
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->each(function (WalletTopup $topup) use ($walletService) {
                if (! $topup->isExpired()) {
                    $walletService->confirmSimulated($topup);
                }
            });

        RefundRequest::query()
            ->where('status', 'pending')
            ->where('method', 'bank')
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->each(fn (RefundRequest $refund) => $refundService->confirmSimulatedBankRefund($refund));

        WalletWithdrawal::query()
            ->where('status', 'pending')
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->each(fn (WalletWithdrawal $withdrawal) => $withdrawalService->confirmSimulated($withdrawal));

        return self::SUCCESS;
    }
}
