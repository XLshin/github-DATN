<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách lịch sử đơn hàng phân loại theo tab Shopee
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $keyword = trim($request->input('keyword', ''));
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

        return view('client.orders.show', compact('order'));
    }

    /**
     * Xử lý hành động hủy đơn hàng từ phía khách hàng
     */
    public function cancel(Request $request, string|int $id): RedirectResponse
    {
        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500', 'min:5'],
        ], [
            'cancel_reason.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
            'cancel_reason.min' => 'Lý do hủy đơn cần cụ thể hơn (tối thiểu 5 ký tự).',
        ]);

        $order = Order::findOrFail($id);

        // Kiểm tra quyền sở hữu
        if ((int)$order->user_id !== (int)auth()->id()) {
            abort(403);
        }

        // Ràng buộc điều kiện hủy đơn: Chỉ cho phép khi đơn hàng ở trạng thái 'pending' hoặc 'waiting_pack'
        if (!in_array($order->fulfillment_status, ['pending', 'waiting_pack'], true)) {
            return redirect()->back()->with('error', 'Đơn hàng đã được chuyển đi hoặc xử lý, không thể tự hủy.');
        }

        // Thực hiện cập nhật trạng thái đồng bộ
        $order->update([
            'status' => 'cancelled', // Cập nhật trạng thái tổng/thanh toán
            'fulfillment_status' => 'cancelled', // Cập nhật trạng thái giao hàng
            'cancelled_at' => now(),
            'cancel_reason' => $request->input('cancel_reason'),
            'cancelled_by' => 'user' // Đánh dấu do khách hàng chủ động hủy
        ]);

        return redirect()->route('orders.index', ['status' => 'cancelled'])
                         ->with('success', 'Đơn hàng của bạn đã được hủy thành công.');
    }
}