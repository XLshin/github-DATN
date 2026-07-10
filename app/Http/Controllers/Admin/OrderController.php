<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'user',
            'items.product',
            'items.variant',
            'items.imei',
            'payment',
            'shipment',
        ])->latest();

        if ($request->filled('tab')) {
            match ($request->tab) {
                'unpaid' => $query->whereHas('payment', function ($q) {
                    $q->where('payment_status', 'pending');
                }),
                'pending' => $query->where('fulfillment_status', 'pending'),
                'waiting_pack' => $query->where('fulfillment_status', 'waiting_pack'),
                'waiting_handover' => $query->where('fulfillment_status', 'waiting_handover'),
                'shipping' => $query->where('fulfillment_status', 'shipping'),
                'completed' => $query->where('fulfillment_status', 'completed'),
                'cancelled' => $query->where('fulfillment_status', 'cancelled'),
                'failed' => $query->where('fulfillment_status', 'failed'),
                default => null,
            };
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('shipping_address', 'like', "%{$keyword}%");
            });
        }

        // Đã xóa dòng $orders bị ghi đè phía dưới để giữ lại kết quả lọc/tìm kiếm
        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'items.product',
            'items.variant',
            'items.imei',
            'payment',
            'shipment',
            'proofs.creator',
        ]);
        return view('admin.orders.show', compact('order'));
    }

    public function confirm(Order $order)
    {
        if ($order->fulfillment_status !== 'pending') {
            return back()->with('error', 'Chỉ đơn hàng chờ xử lý mới được xác nhận.');
        }

        $order->update([
            'status' => 'processing',
            'fulfillment_status' => 'waiting_pack',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Đã xác nhận đơn hàng. Đơn đã chuyển sang chờ đóng gói.');
    }

    public function confirmBankTransfer(Order $order)
    {
        $payment = $order->payment;

        if (! $payment || $payment->payment_method !== 'bank_transfer' || $payment->payment_status === 'paid') {
            return back()->with('error', 'Đơn hàng không ở trạng thái chờ xác nhận chuyển khoản.');
        }

        $payment->update([
            'payment_status' => 'paid',
            'paid_at'        => now(),
        ]);

        return back()->with('success', 'Đã xác nhận nhận được tiền chuyển khoản.');
    }

    public function markPacked(Request $request, Order $order)
    {
        if ($order->fulfillment_status !== 'waiting_pack') {
            return back()->with('error', 'Chỉ đơn hàng chờ đóng gói mới được xác nhận đóng gói.');
        }

        $request->validate([
            'packed_image' => ['required', 'image', 'max:4096'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'packed_image.required' => 'Vui lòng tải ảnh minh chứng đã đóng gói.',
            'packed_image.image' => 'File tải lên phải là hình ảnh.',
        ]);

        DB::transaction(function () use ($request, $order) {
            $path = $request->file('packed_image')->store('order-proofs', 'public');

            OrderProof::create([
                'order_id' => $order->getKey(),
                'type' => 'packed',
                'image_path' => $path,
                'note' => $request->note,
                'created_by' => $request->user()?->getAuthIdentifier(),
            ]);

            $order->update([
                'status' => 'processing',
                'fulfillment_status' => 'waiting_handover',
                'packed_at' => now(),
            ]);
        });

        return back()->with('success', 'Đã xác nhận đóng gói. Đơn đã chuyển sang chờ bàn giao.');
    }

    public function handover(Order $order)
    {
        if ($order->fulfillment_status !== 'waiting_handover') {
            return back()->with('error', 'Chỉ đơn hàng chờ bàn giao mới được chuyển sang đang giao.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'shipping',
                'fulfillment_status' => 'shipping',
                'handed_over_at' => now(),
            ]);

            if ($order->shipment) {
                $order->shipment->update([
                    'shipping_status' => 'shipping',
                    'status' => 'shipping',
                    'shipped_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Đơn hàng đã chuyển sang trạng thái đang giao.');
    }

    public function markDelivered(Request $request, Order $order)
    {
        if ($order->fulfillment_status !== 'shipping') {
            return back()->with('error', 'Chỉ đơn hàng đang giao mới được xác nhận đã giao.');
        }

        $request->validate([
            'delivered_image' => ['required', 'image', 'max:4096'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'delivered_image.required' => 'Vui lòng tải ảnh minh chứng đã giao hàng.',
            'delivered_image.image' => 'File tải lên phải là hình ảnh.',
        ]);

        DB::transaction(function () use ($request, $order) {
            $path = $request->file('delivered_image')->store('order-proofs', 'public');

            OrderProof::create([
                'order_id' => $order->getKey(),
                'type' => 'delivered',
                'image_path' => $path,
                'note' => $request->note,
                'created_by' => $request->user()?->getAuthIdentifier(),
            ]);

            foreach ($order->items as $item) {
                if ($item->imei_id && $item->imei && $item->imei->status === 'reserved') {
                    $item->imei->update([
                        'status' => 'sold',
                        'reserved_at' => null,
                        'reserved_by_order_item_id' => null,
                    ]);
                }
            }

            $order->update([
                'status' => 'completed',
                'fulfillment_status' => 'completed',
                'delivered_at' => now(),
            ]);

            if ($order->payment) {
                $order->payment->update([
                    'payment_status' => 'paid',
                    'paid_at' => $order->payment->paid_at ?? now(),
                ]);
            }

            if ($order->shipment) {
                $order->shipment->update([
                    'shipping_status' => 'delivered',
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Đã xác nhận giao hàng thành công. Đơn hàng đã hoàn thành.');
    }

    public function markFailed(Request $request, Order $order)
    {
        if ($order->fulfillment_status !== 'shipping') {
            return back()->with('error', 'Chỉ đơn hàng đang giao mới được chuyển sang giao thất bại.');
        }

        $request->validate([
            'failed_image' => ['nullable', 'image', 'max:4096'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $order) {
            if ($request->hasFile('failed_image')) {
                $path = $request->file('failed_image')->store('order-proofs', 'public');

                OrderProof::create([
                    'order_id' => $order->getKey(),
                    'type' => 'failed_delivery',
                    'image_path' => $path,
                    'note' => $request->note,
                    'created_by' => $request->user()?->getAuthIdentifier(),
                ]);
            }

            $order->update([
                'status' => 'shipping',
                'fulfillment_status' => 'failed',
            ]);

            if ($order->shipment) {
                $order->shipment->update([
                    'shipping_status' => 'failed',
                    'status' => 'failed',
                ]);
            }
        });

        return back()->with('success', 'Đã cập nhật đơn hàng giao thất bại.');
    }

    public function retryDelivery(Order $order)
    {
        if ($order->fulfillment_status !== 'failed') {
            return back()->with('error', 'Chỉ đơn hàng giao thất bại mới được giao lại.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'shipping',
                'fulfillment_status' => 'shipping',
                'handed_over_at' => now(),
            ]);

            if ($order->shipment) {
                $order->shipment->update([
                    'shipping_status' => 'shipping',
                    'status' => 'shipping',
                    'shipped_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Đơn hàng đã được chuyển sang giao lại.');
    }

    public function cancel(Order $order)
    {
        if (in_array($order->fulfillment_status, ['completed', 'cancelled'], true)) {
            return back()->with('error', 'Không thể hủy đơn hàng đã hoàn thành hoặc đã hủy.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->imei_id && $item->imei && $item->imei->status === 'reserved') {
                    $item->imei->update([
                        'status' => 'available',
                        'reserved_at' => null,
                        'reserved_by_order_item_id' => null,
                    ]);

                    $item->update([
                        'imei_id' => null,
                    ]);
                }

                if ($item->product && $item->product->product_type === 'quantity' && $item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'fulfillment_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            if ($order->shipment) {
                $order->shipment->update([
                    'shipping_status' => 'failed',
                    'status' => 'cancelled',
                ]);
            }
        });

        return back()->with('success', 'Đã hủy đơn hàng và hoàn lại tồn kho/IMEI.');
    }

    public function printShippingLabel(Order $order)
    {
        $order->load([
            'user',
            'items.product',
            'items.variant',
            'items.imei',
            'payment',
        ]);

        $order->update([
            'shipping_label_printed_at' => now(),
        ]);

        return view('admin.orders.shipping-label', compact('order'));
    }
}
