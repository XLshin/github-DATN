<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Imei;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::with('order')
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->keyword;

                $query->where('tracking_code', 'like', "%{$keyword}%")
                    ->orWhereHas('order', function ($q) use ($keyword) {
                        $q->where('order_code', 'like', "%{$keyword}%")
                            ->orWhere('customer_name', 'like', "%{$keyword}%")
                            ->orWhere('customer_phone', 'like', "%{$keyword}%");
                    });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('shipping_status', $request->status);
            })
            ->latest()
            ->paginate(10);

        $ordersCanCreateShipment = Order::where('status', 'processing')
            ->whereDoesntHave('shipment')
            ->latest()
            ->get();

        return view('admin.shipments.index', compact('shipments', 'ordersCanCreateShipment'));
    }

    public function createFromOrder(Order $order)
    {
        if (in_array($order->status, ['completed', 'cancelled', 'shipping'])) {
            return back()->with('error', 'Không thể tạo vận đơn cho đơn đã hoàn tất hoặc đã hủy.');
        }

        if (Shipment::where('order_id', $order->id)->exists()) {
            return back()->with('error', 'Đơn hàng này đã có vận đơn.');
        }

        return view('admin.shipments.create', compact('order'));
    }

    public function storeFromOrder(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_unit' => ['required', 'string', 'max:255'],
            'tracking_code' => ['nullable', 'string', 'max:255'],
        ]);

        if (in_array($order->status, ['completed', 'cancelled', 'shipping'])) {
            return back()->with('error', 'Không thể tạo vận đơn cho đơn đã hoàn tất, đang vận chuyển hoặc đã hủy.');
        }

        if (Shipment::where('order_id', $order->id)->exists()) {
            return back()->with('error', 'Đơn hàng này đã có vận đơn.');
        }

        DB::transaction(function () use ($validated, $order) {
            Shipment::create([
                'order_id' => $order->id,
                'shipping_unit' => $validated['shipping_unit'],
                'tracking_code' => $validated['tracking_code'] ?? null,
                'shipping_status' => 'pending',
            ]);

            $order->update([
                'status' => 'shipping',
            ]);
        });

        return redirect()
            ->route('admin.shipments.index')
            ->with('success', 'Tạo vận đơn thành công.');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load('order');

        $histories = $this->buildShipmentHistory($shipment);

        return view('admin.shipments.show', compact('shipment', 'histories'));
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'shipping_status' => [
                'required',
                Rule::in(['pending', 'shipping', 'delivered', 'failed']),
            ],
        ]);

        DB::transaction(function () use ($validated, $shipment) {
            $status = $validated['shipping_status'];

            $data = [
                'shipping_status' => $status,
            ];

            if ($status === 'shipping' && is_null($shipment->shipped_at)) {
                $data['shipped_at'] = now();

                // decrement stock safely for each order item
                foreach ($shipment->order->items as $item) {
                    $variant = $item->variant()->lockForUpdate()->first();
                    if ($variant) {
                        if ($variant->stock_quantity < $item->quantity) {
                            throw new \RuntimeException('Không đủ tồn kho để chuyển đơn hàng.');
                        }
                        $variant->stock_quantity -= $item->quantity;
                        $variant->save();
                    }
                }
            }

            if ($status === 'delivered') {
                $data['shipped_at'] = $shipment->shipped_at ?? now();
                $data['delivered_at'] = now();
            }

            $shipment->update($data);

            if ($status === 'delivered') {
                $shipment->order->update([
                    'status' => 'completed',
                ]);
            } elseif ($status === 'failed') {
                // Rollback stock and IMEIs (idempotent): if order_item has imei_id, return it to available and increment stock.
                foreach ($shipment->order->items as $item) {
                    if ($item->imei_id) {
                        // revert IMEI
                        $imei = Imei::lockForUpdate()->find($item->imei_id);
                        if ($imei) {
                            $imei->status = 'available';
                            $imei->reserved_by_order_item_id = null;
                            $imei->reserved_at = null;
                            $imei->save();
                        }

                        // increment variant stock
                        $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                        if ($variant) {
                            $variant->stock_quantity += $item->quantity;
                            $variant->save();
                        }

                        // detach imei from order item
                        $item->imei_id = null;
                        $item->save();
                    }
                }

                // mark order as returned so admin can handle restocking/notifications
                $shipment->order->update([
                    'status' => 'returned',
                ]);
            } else {
                $shipment->order->update([
                    'status' => 'shipping',
                ]);
            }
        });

        return back()->with('success', 'Cập nhật trạng thái giao hàng thành công.');
    }

    public function lookup(Request $request)
    {
        $shipment = null;

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $shipment = Shipment::with('order')
                ->where('tracking_code', $keyword)
                ->orWhereHas('order', function ($q) use ($keyword) {
                    $q->where('order_code', $keyword)
                        ->orWhere('customer_phone', $keyword);
                })
                ->first();
        }

        return view('admin.shipments.lookup', compact('shipment'));
    }

    private function buildShipmentHistory(Shipment $shipment): array
    {
        $histories = [];

        if ($shipment->created_at) {
            $histories[] = [
                'time' => $shipment->created_at,
                'title' => 'Tạo vận đơn',
                'description' => 'Vận đơn được tạo cho đơn hàng.',
            ];
        }

        if ($shipment->shipped_at) {
            $histories[] = [
                'time' => $shipment->shipped_at,
                'title' => 'Bắt đầu giao hàng',
                'description' => 'Đơn hàng đã được chuyển sang trạng thái đang giao.',
            ];
        }

        if ($shipment->delivered_at) {
            $histories[] = [
                'time' => $shipment->delivered_at,
                'title' => 'Giao hàng thành công',
                'description' => 'Đơn hàng đã được giao thành công.',
            ];
        }

        if ($shipment->shipping_status === 'failed') {
            $histories[] = [
                'time' => $shipment->updated_at,
                'title' => 'Giao hàng thất bại',
                'description' => 'Trạng thái hiện tại của vận đơn là giao thất bại.',
            ];
        }

        if ($shipment->updated_at && $shipment->updated_at->ne($shipment->created_at)) {
            $histories[] = [
                'time' => $shipment->updated_at,
                'title' => 'Cập nhật gần nhất',
                'description' => 'Thông tin vận chuyển được cập nhật lần gần nhất.',
            ];
        }

        return $histories;
    }
}
