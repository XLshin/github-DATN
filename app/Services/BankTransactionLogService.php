<?php

namespace App\Services;

use App\Models\BankTransactionLog;
use App\Models\User;

class BankTransactionLogService
{
    /**
     * Ghi 1 dòng vào sổ nhật ký giao dịch ngân hàng tập trung — dùng cho mọi loại giao dịch
     * (nạp ví, rút tiền, hoàn tiền, thanh toán đơn hàng) để tra cứu/quản lý ở một nơi duy nhất.
     */
    public function log(array $data): BankTransactionLog
    {
        return BankTransactionLog::create(array_merge([
            'occurred_at' => now(),
        ], $data));
    }

    public function logTopup(\App\Models\WalletTopup $topup, string $status, ?User $handledBy = null, ?string $note = null): BankTransactionLog
    {
        return $this->log([
            'type' => 'topup',
            'direction' => 'in',
            'user_id' => $topup->user_id,
            'reference_type' => \App\Models\WalletTopup::class,
            'reference_id' => $topup->id,
            'amount' => $topup->amount,
            'payment_method' => $topup->payment_method,
            'status' => $status,
            'transaction_code' => $topup->transaction_code,
            'proof_image' => $topup->proof_image,
            'handled_by' => $handledBy?->id,
            'note' => $note,
        ]);
    }

    public function logWithdrawal(\App\Models\WalletWithdrawal $withdrawal, string $status, ?User $handledBy = null, ?string $note = null): BankTransactionLog
    {
        return $this->log([
            'type' => 'withdrawal',
            'direction' => 'out',
            'user_id' => $withdrawal->user_id,
            'reference_type' => \App\Models\WalletWithdrawal::class,
            'reference_id' => $withdrawal->id,
            'amount' => $withdrawal->amount,
            'payment_method' => 'bank_transfer',
            'bank_name' => $withdrawal->bank_name,
            'account_number' => $withdrawal->account_number,
            'account_holder_name' => $withdrawal->account_holder_name,
            'status' => $status,
            'transaction_code' => $withdrawal->transaction_code,
            'proof_image' => $withdrawal->proof_image,
            'handled_by' => $handledBy?->id,
            'note' => $note,
        ]);
    }

    public function logRefund(\App\Models\RefundRequest $refund, string $status, ?User $handledBy = null, ?string $note = null): BankTransactionLog
    {
        return $this->log([
            'type' => 'refund',
            'direction' => 'out',
            'user_id' => $refund->user_id,
            'reference_type' => \App\Models\RefundRequest::class,
            'reference_id' => $refund->id,
            'amount' => $refund->amount,
            'payment_method' => $refund->method === 'wallet' ? 'wallet' : 'bank_transfer',
            'bank_name' => $refund->bank_name,
            'account_number' => $refund->bank_account_number,
            'account_holder_name' => $refund->bank_account_name,
            'status' => $status,
            'proof_image' => $refund->proof_image,
            'handled_by' => $handledBy?->id,
            'note' => $note,
        ]);
    }

    public function logOrderPayment(\App\Models\Payment $payment, string $status, ?User $handledBy = null, ?string $note = null): BankTransactionLog
    {
        return $this->log([
            'type' => 'order_payment',
            'direction' => 'in',
            'user_id' => $payment->order->user_id,
            'reference_type' => \App\Models\Payment::class,
            'reference_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'status' => $status,
            'transaction_code' => $payment->transaction_code,
            'proof_image' => $payment->proof_image,
            'handled_by' => $handledBy?->id,
            'note' => $note,
        ]);
    }
}
