<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Imei;
use App\Models\Order;
use App\Models\OrderProof;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'user',
            'receiver',
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
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('shipping_address', 'like', "%{$keyword}%")
                    ->orWhereHas('receiver', function ($receiverQuery) use ($keyword) {
                        $receiverQuery->where('receiver_name', 'like', "%{$keyword}%")
                            ->orWhere('receiver_phone', 'like', "%{$keyword}%")
                            ->orWhere('receiver_address', 'like', "%{$keyword}%");
                    });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $availableImeisByVariant = $this->getAvailableImeisByVariant($orders->getCollection());

        return view('admin.orders.index', compact('orders', 'availableImeisByVariant'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'receiver',
            'items.product',
            'items.variant',
            'items.imei',
            'payment',
            'shipment',
            'proofs.creator',
        ]);

        $availableImeisByVariant = $this->getAvailableImeisByVariant(collect([$order]));

        return view('admin.orders.show', compact('order', 'availableImeisByVariant'));
    }

    public function updateReceiver(Request $request, Order $order)
    {
        $data = $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:30'],
            'receiver_address' => ['required', 'string', 'max:1000'],
            'receiver_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'receiver_name.required' => 'Vui lòng nhập tên người nhận.',
            'receiver_phone.required' => 'Vui lòng nhập số điện thoại người nhận.',
            'receiver_address.required' => 'Vui lòng nhập địa chỉ người nhận.',
        ]);

        $order->receiver()->updateOrCreate(
            ['order_id' => $order->getKey()],
            $data
        );

        return back()->with('success', 'Đã cập nhật thông tin người nhận.');
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

    public function markPacked(Request $request, Order $order)
    {
        if ($order->fulfillment_status !== 'waiting_pack') {
            return back()->with('error', 'Chỉ đơn hàng chờ đóng gói mới được xác nhận đóng gói.');
        }

        $request->validate([
            'packed_image' => ['required', 'image', 'max:4096'],
            'note' => ['nullable', 'string', 'max:1000'],
            'imei_values' => ['nullable', 'array'],
            'imei_values.*' => ['nullable', 'string', 'max:20'],
        ], [
            'packed_image.required' => 'Vui lòng tải ảnh minh chứng đã đóng gói.',
            'packed_image.image' => 'File tải lên phải là hình ảnh.',
        ]);

        $order->load([
            'items.product',
            'items.variant',
            'items.imei',
        ]);

        $imeiItems = $order->items->filter(fn ($item) => $this->orderItemNeedsImei($item));

        $submittedImeis = $this->validatePackedImeis($request, $imeiItems);

        DB::transaction(function () use ($request, $order, $imeiItems, $submittedImeis) {
            foreach ($imeiItems as $item) {
                if ($item->imei_id) {
                    continue;
                }

                $imeiValue = $submittedImeis[$item->getKey()] ?? null;

                $imei = Imei::query()
                    ->where('imei', $imeiValue)
                    ->lockForUpdate()
                    ->first();

                if (!$imei) {
                    throw ValidationException::withMessages([
                        "imei_values.{$item->getKey()}" => "IMEI {$imeiValue} không tồn tại trong hệ thống.",
                    ]);
                }

                if ((int) $imei->product_variant_id !== (int) $item->product_variant_id) {
                    throw ValidationException::withMessages([
                        "imei_values.{$item->getKey()}" => "IMEI {$imeiValue} không đúng biến thể khách đã đặt.",
                    ]);
                }

                if ($imei->status !== 'available') {
                    throw ValidationException::withMessages([
                        "imei_values.{$item->getKey()}" => "IMEI {$imeiValue} không còn ở trạng thái available.",
                    ]);
                }

                $imei->reserveForOrderItem($item);
            }

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

        return back()->with('success', 'Đã xác nhận đóng gói, gán IMEI và chuyển đơn sang chờ bàn giao.');
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
            $order->loadMissing('items.imei');

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
                    $item->imei->markAsSold();
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
            $order->loadMissing('items.product', 'items.variant', 'items.imei', 'shipment');

            foreach ($order->items as $item) {
                if ($item->imei_id && $item->imei && $item->imei->status === 'reserved') {
                    $item->imei->releaseReservation();

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
        'receiver',
    ]);

    if ($this->orderHasMissingRequiredImeis($order)) {
        return back()->with(
            'error',
            'Đơn hàng có sản phẩm cần IMEI nhưng chưa được gán IMEI. Vui lòng xác nhận đóng gói và nhập IMEI trước khi in phiếu giao hàng.'
        );
    }

    $order->update([
        'shipping_label_printed_at' => now(),
    ]);

    return view('admin.orders.shipping-label', compact('order'));
}

private function orderHasMissingRequiredImeis(Order $order): bool
{
    return $order->items->contains(function ($item) {
        return ($item->product->product_type ?? null) === 'imei/serial'
            && empty($item->imei_id);
    });
}

    private function getAvailableImeisByVariant(Collection $orders): Collection
    {
        $variantIds = $orders
            ->flatMap(function (Order $order) {
                return $order->items
                    ->filter(fn ($item) => $this->orderItemNeedsImei($item) && !$item->imei_id)
                    ->pluck('product_variant_id');
            })
            ->filter()
            ->unique()
            ->values();

        if ($variantIds->isEmpty()) {
            return collect();
        }

        return Imei::query()
            ->whereIn('product_variant_id', $variantIds)
            ->where('status', 'available')
            ->orderBy('imei')
            ->get()
            ->groupBy('product_variant_id');
    }

    private function orderItemNeedsImei($item): bool
    {
        return ($item->product->product_type ?? null) === 'imei/serial';
    }

    private function validatePackedImeis(Request $request, Collection $imeiItems): array
    {
        $errors = [];
        $submittedImeis = [];
        $seenImeis = [];

        foreach ($imeiItems as $item) {
            if ((int) $item->quantity !== 1) {
                $errors["imei_values.{$item->getKey()}"] = 'Sản phẩm quản lý theo IMEI chỉ nên có số lượng 1 trên mỗi dòng order_item. Nếu khách mua nhiều máy cùng biến thể, hãy tách thành nhiều dòng order_item, mỗi dòng quantity = 1.';
                continue;
            }

            if ($item->imei_id) {
                continue;
            }

            $imeiValue = trim((string) $request->input("imei_values.{$item->getKey()}", ''));

            if ($imeiValue === '') {
                $productName = $item->product->name ?? 'sản phẩm';
                $errors["imei_values.{$item->getKey()}"] = "Vui lòng nhập IMEI cho {$productName}.";
                continue;
            }

            $duplicateKey = strtolower($imeiValue);

            if (isset($seenImeis[$duplicateKey])) {
                $errors["imei_values.{$item->getKey()}"] = "IMEI {$imeiValue} đang được nhập trùng trong cùng đơn hàng.";
                continue;
            }

            $seenImeis[$duplicateKey] = true;
            $submittedImeis[$item->getKey()] = $imeiValue;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $submittedImeis;
    }
}