<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\WalletTopup;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Collection;

/**
 * Gom các yêu cầu từ khách hàng đang chờ admin xử lý (nạp ví, rút tiền, hoàn tiền, xác nhận
 * thanh toán đơn hàng) — dùng chung cho chuông thông báo header và endpoint polling JS.
 */
class PendingAdminRequestsService
{
    public function get(int $limitPerType = 10): Collection
    {
        $items = collect();

        WalletTopup::with('user')
            ->where('payment_status', 'pending')
            ->whereNotNull('proof_image')
            ->latest()
            ->take($limitPerType)
            ->get()
            ->each(fn (WalletTopup $t) => $items->push([
                'id' => 'topup-' . $t->id,
                'type' => 'Nạp ví',
                'icon' => 'bi-wallet2',
                'user' => $t->user->name ?? '-',
                'amount' => (float) $t->amount,
                'time' => $t->updated_at,
                'url' => route('admin.wallet-topups.show', $t),
            ]));

        WalletWithdrawal::with('user')
            ->whereIn('status', ['pending', 'processing'])
            ->latest('requested_at')
            ->take($limitPerType)
            ->get()
            ->each(fn (WalletWithdrawal $w) => $items->push([
                'id' => 'withdrawal-' . $w->id,
                'type' => 'Rút tiền',
                'icon' => 'bi-arrow-down-circle',
                'user' => $w->user->name ?? '-',
                'amount' => (float) $w->amount,
                'time' => $w->requested_at,
                'url' => route('admin.wallet-withdrawals.show', $w),
            ]));

        RefundRequest::with('user')
            ->where('method', 'bank')
            ->whereIn('status', ['pending', 'processing'])
            ->latest('requested_at')
            ->take($limitPerType)
            ->get()
            ->each(fn (RefundRequest $r) => $items->push([
                'id' => 'refund-' . $r->id,
                'type' => 'Hoàn tiền',
                'icon' => 'bi-arrow-return-left',
                'user' => $r->user->name ?? '-',
                'amount' => (float) $r->amount,
                'time' => $r->requested_at,
                'url' => route('admin.refunds.show', $r),
            ]));

        Payment::with('order.user')
            ->where('payment_status', 'pending')
            ->whereNotNull('proof_image')
            ->latest('updated_at')
            ->take($limitPerType)
            ->get()
            ->each(fn (Payment $p) => $items->push([
                'id' => 'payment-' . $p->id,
                'type' => 'Thanh toán đơn hàng',
                'icon' => 'bi-receipt',
                'user' => $p->order->user->name ?? $p->payer_name ?? '-',
                'amount' => (float) $p->amount,
                'time' => $p->updated_at,
                'url' => route('admin.orders.show', $p->order_id),
            ]));

        return $items->sortByDesc('time')->values();
    }
}
