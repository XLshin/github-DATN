<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly RefundService $refundService,
    ) {}

    /**
     * Hiển thị danh sách lịch sử đơn hàng phân loại theo tab Shopee
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $keyword = trim((string) $request->input('keyword'));
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        // Khởi tạo truy vấn đơn hàng của chính user đang đăng nhập
        $query = auth()->user()->orders()->with(['items.product', 'items.variant']);
        
        // Lọc theo trạng thái tab nếu tham số status hợp lệ
        if (in_array($status, ['pending', 'shipping', 'completed', 'failed', 'cancelled'], true)) {

            if ($status === 'shipping') {

                $query->whereIn('fulfillment_status', [
                    'waiting_pack',
                    'waiting_handover',
                    'shipping'
                ]);

            } elseif ($status === 'failed') {

                $query->where('fulfillment_status', 'failed');

            } else {

                $query->where('fulfillment_status', $status);

            }
        }

        // Tìm theo mã đơn hoặc tên sản phẩm
        if (!empty($keyword)) {

            $query->where(function ($q) use ($keyword) {

                $q->where('order_code', 'like', "%{$keyword}%")
                ->orWhereHas('items.product', function ($productQuery) use ($keyword) {

                    $productQuery->where('name', 'like', "%{$keyword}%");

                });

            });

        }

        // Lọc từ ngày
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        // Lọc đến ngày
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }
        
        $orders = $query->latest()->paginate(5)->withQueryString();
        
        return view('client.orders.index', compact(
            'orders',
            'status',
            'keyword',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Hiển thị trang chi tiết đơn hàng, vận đơn và tiến trình giao hàng
     */
    public function show(string|int $id): View
    {
        // Eager load toàn bộ các thực thể liên quan: sản phẩm, biến thể, vận đơn, người nhận, bằng chứng
        $order = Order::with([
            'items.product.images',
            'items.variant.images',
            'items.variant',
            'items.imeis',

            'shipment.carrier',
            'payment',
            'receiver',
            'proofs'
        ])->findOrFail($id);

        // Kiểm tra quyền sở hữu đơn hàng bảo mật
        if ((int)$order->user_id !== (int)auth()->id()) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        // Tài khoản ngân hàng đã liên kết (nếu có) để tự điền nhanh khi chọn hoàn tiền qua ngân hàng
        $bankAccounts = auth()->user()->bankAccounts()->orderByDesc('is_default')->latest()->get();

        return view('client.orders.show', compact('order', 'bankAccounts'));
    }

    /**
     * Trả về trạng thái hiện tại của đơn hàng (nhẹ, không kèm quan hệ) — dùng cho JS polling ở
     * trang chi tiết đơn để tự phát hiện thay đổi (xác nhận/hủy/đóng gói...) mà không cần khách
     * phải bấm tải lại trang.
     */
    public function statusCheck(string|int $id)
    {
        $order = Order::select(['id', 'user_id', 'status', 'fulfillment_status', 'updated_at'])
            ->with(['payment:id,order_id,payment_status'])
            ->findOrFail($id);

        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        return response()->json([
            'status' => $order->status,
            'fulfillment_status' => $order->fulfillment_status,
            'payment_status' => $order->payment?->payment_status,
            'updated_at' => $order->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Xử lý hành động hủy đơn hàng từ phía khách hàng
     */
    public function cancel(Request $request, string|int $id): RedirectResponse
    {
        $order = Order::with('payment')->findOrFail($id);

        // Kiểm tra quyền sở hữu
        if ((int)$order->user_id !== (int)auth()->id()) {
            abort(403);
        }

        $needsRefund = $order->payment && $order->payment->payment_status === 'paid';

        $rules = [
            'cancel_reason' => ['required', 'string', 'max:500', 'min:5'],
        ];

        if ($needsRefund) {
            $rules['refund_method'] = ['required', 'string', 'in:wallet,bank'];
            $rules['bank_name'] = ['required_if:refund_method,bank', 'nullable', 'string', 'max:255'];
            $rules['bank_account_number'] = ['required_if:refund_method,bank', 'nullable', 'string', 'max:50'];
            $rules['bank_account_name'] = ['required_if:refund_method,bank', 'nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules, [
            'cancel_reason.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
            'cancel_reason.min' => 'Lý do hủy đơn cần cụ thể hơn (tối thiểu 5 ký tự).',
            'refund_method.required' => 'Vui lòng chọn phương thức nhận hoàn tiền.',
        ]);

        // Ràng buộc điều kiện hủy đơn: Chỉ cho phép khi đơn hàng ở trạng thái 'pending' hoặc 'waiting_pack'
        if (!in_array($order->fulfillment_status, ['pending', 'waiting_pack'], true)) {
            return redirect()->back()->with('error', 'Đơn hàng đã được chuyển đi hoặc xử lý, không thể tự hủy.');
        }

        try {
            DB::transaction(function () use ($order, $request, $validated, $needsRefund) {
                $this->checkoutService->restoreInventoryForCancelledOrder($order);

                $order->update([
                    'status' => 'cancelled',
                    'fulfillment_status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancel_reason' => $validated['cancel_reason'],
                    'cancelled_by' => 'user',
                ]);

                if ($needsRefund) {
                    $this->refundService->request(
                        $order,
                        $request->user(),
                        $validated['refund_method'],
                        [
                            'bank_name' => $validated['bank_name'] ?? null,
                            'bank_account_number' => $validated['bank_account_number'] ?? null,
                            'bank_account_name' => $validated['bank_account_name'] ?? null,
                        ]
                    );
                }
            });
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return redirect()->back()->with('error', $firstError);
        }

        $message = $needsRefund
            ? 'Đơn hàng đã được hủy. Yêu cầu hoàn tiền của bạn đã được ghi nhận.'
            : 'Đơn hàng của bạn đã được hủy thành công.';

        return redirect()->route('orders.index', ['status' => 'cancelled'])
                         ->with('success', $message);
    }
}