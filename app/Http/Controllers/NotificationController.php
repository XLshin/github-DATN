<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\WalletTopup;
use App\Models\WalletWithdrawal;
use App\Services\CheckoutService;
use App\Services\PaymentWebhookService;
use App\Services\RefundService;
use App\Services\WalletService;
use App\Services\WalletWithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct(
        private readonly PaymentWebhookService $paymentWebhookService,
        private readonly WalletService $walletService,
        private readonly RefundService $refundService,
        private readonly WalletWithdrawalService $withdrawalService,
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $notifications = $user->notifications()->paginate(20);

        return view('client.notifications.index', compact('notifications'));
    }

    /**
     * Endpoint được navbar poll định kỳ (mỗi ~25s, trên mọi trang) để cập nhật số badge và danh
     * sách thả xuống mà không cần tải lại trang. Tiện thể xác nhận luôn các giao dịch mô phỏng
     * (thanh toán/nạp ví/hoàn tiền) của user đã đến hạn simulate_confirm_at — nhờ vậy các thông
     * báo tự động (tiền về ví, hoàn tiền xong...) luôn xuất hiện trong ~25s bất kể user có đang
     * mở đúng trang chi tiết đơn hàng/ví hay cron `schedule:run` có được cấu hình hay không.
     */
    public function recent(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $this->confirmDueSimulatedTransactions($user);

        $notifications = $user->notifications()->latest()->take(8)->get()->map(function (DatabaseNotification $notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'other',
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'icon' => $notification->data['icon'] ?? 'bi-bell',
                'read' => $notification->read_at !== null,
                'time' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    private function confirmDueSimulatedTransactions(User $user): void
    {
        Payment::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->where('payment_status', 'pending')
            ->whereIn('payment_method', CheckoutService::AUTO_CONFIRM_METHODS)
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->get()
            ->each(function (Payment $payment) {
                if (! $payment->isExpired()) {
                    $this->paymentWebhookService->confirmSimulatedBankTransfer($payment);
                }
            });

        WalletTopup::query()
            ->where('user_id', $user->id)
            ->where('payment_status', 'pending')
            ->whereIn('payment_method', WalletService::AUTO_CONFIRM_METHODS)
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->get()
            ->each(function (WalletTopup $topup) {
                if (! $topup->isExpired()) {
                    $this->walletService->confirmSimulated($topup);
                }
            });

        RefundRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('method', 'bank')
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->get()
            ->each(fn (RefundRequest $refund) => $this->refundService->confirmSimulatedBankRefund($refund));

        WalletWithdrawal::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('simulate_confirm_at')
            ->where('simulate_confirm_at', '<=', now())
            ->get()
            ->each(fn (WalletWithdrawal $withdrawal) => $this->withdrawalService->confirmSimulated($withdrawal));
    }

    public function markRead(Request $request, string $notification)
    {
        /** @var User $user */
        $user = $request->user();

        $record = $user->notifications()->whereKey($notification)->first();
        $record?->markAsRead();

        $url = $record?->data['url'] ?? route('notifications.index');

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : redirect($url);
    }

    public function markAllRead(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : back();
    }
}
