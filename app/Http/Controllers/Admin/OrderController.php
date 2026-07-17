<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Imei;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderProof;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
            'items.imeis',
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
            'items.imeis',
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
            'imei_values.*' => ['nullable', 'array'],
            'imei_values.*.*' => ['nullable', 'string', 'max:20'],
        ], [
            'packed_image.required' => 'Vui lòng tải ảnh minh chứng đã đóng gói.',
            'packed_image.image' => 'File tải lên phải là hình ảnh.',
        ]);

        $order->load([
            'items.product',
            'items.variant',
            'items.imeis',
        ]);

        $imeiItems = $order->items->filter(fn ($item) => $this->orderItemNeedsImei($item));

        $submittedImeis = $this->validatePackedImeis($request, $imeiItems);

        DB::transaction(function () use ($request, $order, $imeiItems, $submittedImeis) {
            foreach ($imeiItems as $item) {
                $imeiValues = $submittedImeis[$item->getKey()] ?? [];

                foreach ($imeiValues as $imeiValue) {
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
            $order->loadMissing('items.imeis');

            $path = $request->file('delivered_image')->store('order-proofs', 'public');

            OrderProof::create([
                'order_id' => $order->getKey(),
                'type' => 'delivered',
                'image_path' => $path,
                'note' => $request->note,
                'created_by' => $request->user()?->getAuthIdentifier(),
            ]);

            foreach ($order->items as $item) {
                foreach ($item->imeis as $imei) {
                    if ($imei->status === 'reserved') {
                        $imei->markAsSold();
                    }
                }
            }

            $order->update([
                'status' => 'completed',
                'fulfillment_status' => 'completed',
                'delivered_at' => now(),
            ]);

            // Cập nhật tổng chi tiêu của khách hàng
            if ($order->user && $order->user->isCustomer()) {
                $order->user->increment('total_spent', $order->total_amount);
            }

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

    public function cancel(Request $request, Order $order)
{
    $request->validate([
        'cancel_reason' => [
            'required',
            'string',
            'max:1000',
        ],
        'cancel_image' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:4096',
        ],
    ], [
        'cancel_reason.required' => 'Vui lòng nhập lý do hủy đơn.',
        'cancel_image.image' => 'File tải lên phải là hình ảnh.',
    ]);

    if (in_array($order->fulfillment_status, ['completed', 'cancelled'], true)) {
        return back()->with('error', 'Không thể hủy đơn hàng đã hoàn thành hoặc đã hủy.');
    }

    DB::transaction(function () use ($order, $request) {

        $order->loadMissing(
            'items.product',
            'items.variant',
            'items.imeis',
            'shipment',
            'proofs'
        );

        $shouldRestoreInventory = $order->status !== 'returned';

        foreach ($order->items as $item) {
            $returnedImeis = 0;

            if ($shouldRestoreInventory) {
                foreach ($item->imeis as $imei) {
                    if ($imei->status === 'reserved') {
                        $imei->releaseReservation();
                        $returnedImeis++;
                    }
                }

                if ($returnedImeis > 0) {
                    InventoryTransaction::create([
                        'product_variant_id' => $item->product_variant_id,
                        'type' => 'return',
                        'quantity' => $returnedImeis,
                        'note' => 'Trả IMEI về kho do hủy đơn: ' . $order->order_code,
                    ]);
                }
            }

            if (
                $shouldRestoreInventory &&
                $item->product &&
                $item->product->product_type === 'quantity' &&
                $item->variant
            ) {
                $item->variant->increment(
                    'stock_quantity',
                    $item->quantity
                );

                InventoryTransaction::create([
                    'product_variant_id' => $item->product_variant_id,
                    'type' => 'return',
                    'quantity' => (int) $item->quantity,
                    'note' => 'Trả hàng về kho do hủy đơn: ' . $order->order_code,
                ]);
            }

        }

        $order->update([

            'status' => 'cancelled',

            'fulfillment_status' => 'cancelled',

            'cancelled_at' => now(),

            'cancel_reason' => $request->cancel_reason,

            'cancelled_by' => 'admin',

        ]);

        if ($request->hasFile('cancel_image')) {

            $path = $request->file('cancel_image')->store(
                'order-proofs',
                'public'
            );

            OrderProof::create([

                'order_id' => $order->id,

                'type' => 'cancelled',

                'image_path' => $path,

                'note' => $request->cancel_reason,

                'created_by' => Auth::id(),

            ]);
        }

        if ($order->shipment) {

            $order->shipment->update([

                'shipping_status' => 'failed',

                'status' => 'cancelled',

            ]);

        }

    });

    return back()->with(
        'success',
        'Đã hủy đơn hàng và hoàn lại tồn kho/IMEI.'
    );
}

    public function printShippingLabel(Order $order)
{
    $order->load([
        'user',
        'items.product',
        'items.variant',
        'items.imeis',
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
        return $this->orderItemNeedsImei($item) && !$item->hasFullImeiAssignment();
    });
}

    private function getAvailableImeisByVariant(Collection $orders): Collection
    {
        $variantIds = $orders
            ->flatMap(function (Order $order) {
                return $order->items
                    ->filter(fn ($item) => $this->orderItemNeedsImei($item) && !$item->hasFullImeiAssignment())
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
        $seenImeisInOrder = [];

        foreach ($imeiItems as $item) {
            $remaining = $item->remainingImeiSlots();

            if ($remaining === 0) {
                // Item này đã được gán đủ IMEI từ trước, bỏ qua.
                continue;
            }

            $rawValues = (array) $request->input("imei_values.{$item->getKey()}", []);

            // Loại bỏ khoảng trắng và giá trị rỗng.
            $values = array_values(array_filter(
                array_map(fn ($v) => trim((string) $v), $rawValues),
                fn ($v) => $v !== ''
            ));

            $productName = $item->product->name ?? 'sản phẩm';

            if (count($values) !== $remaining) {
                $errors["imei_values.{$item->getKey()}"] = "Vui lòng nhập đủ {$remaining} IMEI cho {$productName} (đã nhập " . count($values) . ").";
                continue;
            }

            $seenInItem = [];

            foreach ($values as $imeiValue) {
                $duplicateKey = strtolower($imeiValue);

                if (isset($seenInItem[$duplicateKey])) {
                    $errors["imei_values.{$item->getKey()}"] = "IMEI {$imeiValue} bị nhập trùng trong cùng {$productName}.";
                    continue 2;
                }

                if (isset($seenImeisInOrder[$duplicateKey])) {
                    $errors["imei_values.{$item->getKey()}"] = "IMEI {$imeiValue} đang được nhập trùng trong cùng đơn hàng.";
                    continue 2;
                }

                $seenInItem[$duplicateKey] = true;
                $seenImeisInOrder[$duplicateKey] = true;
            }

            $submittedImeis[$item->getKey()] = $values;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $submittedImeis;
    }
}
