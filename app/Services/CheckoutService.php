<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Imei;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReceiver;
use App\Models\Payment;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /** Phương thức thanh toán trực tuyến có giới hạn thời gian phiên giao dịch. */
    private const EXPIRING_METHODS = ['card', 'momo', 'vnpay'];

    private const PAYMENT_EXPIRY_MINUTES = 15;

    public function __construct(
        private readonly CartService $cartService,
        private readonly PointService $pointService,
    ) {}

    /**
     * @param  array<int, int>|null  $itemIds  Chỉ thanh toán đúng các dòng giỏ hàng này (null = toàn bộ giỏ).
     */
    public function process(User $user, array $data, ?array $itemIds = null): Order
    {
        $items = $this->cartService->getItems($user, $itemIds);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng trống.',
            ]);
        }

        return DB::transaction(function () use ($user, $data, $items, $itemIds) {
            $subtotal = $this->cartService->calculateTotal($items);
            $coupon = null;
            $couponDiscount = 0;

            // 1. Xử lý giảm giá từ Coupon / Voucher
            if (! empty($data['coupon_id'])) {
                // Khoá dòng voucher để hạn mức sử dụng không bị vượt khi nhiều
                // khách thanh toán cùng lúc.
                $coupon = Coupon::query()->lockForUpdate()->findOrFail($data['coupon_id']);

                if (! $user->coupons()->whereKey($coupon->id)->exists()) {
                    throw ValidationException::withMessages([
                        'coupon_id' => 'Voucher không hợp lệ hoặc bạn không có quyền sử dụng.',
                    ]);
                }

                if (! $coupon->isValidForAmount($subtotal)) {
                    throw ValidationException::withMessages([
                        'coupon_id' => 'Mã voucher không đáp ứng điều kiện tối thiểu.',
                    ]);
                }

                $couponDiscount = $coupon->discountAmount($subtotal);
            }

            // 2. Xử lý giảm giá bằng Điểm thưởng (Points)
            $pointsToUse = (int) ($data['points_to_use'] ?? 0);
            $pointsDiscount = 0;

            if ($pointsToUse > 0) {
                if ((int) $user->points < $pointsToUse) {
                    throw ValidationException::withMessages([
                        'points_to_use' => 'Bạn không có đủ điểm để đổi.',
                    ]);
                }

                $pointsToUse = min($pointsToUse, (int) floor(max($subtotal - $couponDiscount, 0)));
                $pointsDiscount = $pointsToUse;
            }

            $totalAmount = max($subtotal - $couponDiscount - $pointsDiscount, 0);
            $buyerType = $data['buyer_type'] ?? 'self';

            // 3. Khởi tạo đơn hàng (Order)
            $order = Order::query()->create([
                'user_id' => $user->id,
                'buyer_type' => $buyerType,
                'buyer_name' => $buyerType === 'proxy' ? ($data['buyer_name'] ?? null) : null,
                'buyer_phone' => $buyerType === 'proxy' ? ($data['buyer_phone'] ?? null) : null,
                'order_code' => $this->generateOrderCode(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'subtotal' => $subtotal,
                'membership_discount' => 0,
                'coupon_discount' => $couponDiscount,
                'points_used' => $pointsToUse,
                'points_discount' => $pointsDiscount,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'fulfillment_status' => 'pending',
            ]);

            // 4. Khởi tạo thông tin người nhận (OrderReceiver)
            OrderReceiver::query()->create([
                'order_id' => $order->id,
                'receiver_name' => $data['customer_name'],
                'receiver_phone' => $data['customer_phone'],
                'receiver_address' => $data['shipping_address'],
                'receiver_note' => $buyerType === 'proxy'
                    ? 'Đặt hộ bởi: ' . ($data['buyer_name'] ?? '') . ' (' . ($data['buyer_phone'] ?? '') . ')'
                    : null,
            ]);

            // 5. Xử lý từng dòng sản phẩm (OrderItem) và Tồn kho
            foreach ($items as $item) {
                $product = $item->product;
                $variant = $item->productVariant ?? $item->variant; 

                if (! $product) {
                    throw ValidationException::withMessages([
                        'product' => 'Có sản phẩm trong giỏ hàng không còn tồn tại.',
                    ]);
                }

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'variant' => 'Có biến thể sản phẩm trong giỏ hàng không còn tồn tại.',
                    ]);
                }

                $quantity = (int) $item->quantity;
                $price = method_exists($this->cartService, 'unitPrice') 
                    ? $this->cartService->unitPrice($item) 
                    : $this->cartService->itemUnitPrice($item);
                $productType = $product->product_type;

                if ($productType === 'imei/serial') {
                    // Bước A: Kiểm tra xem tổng số IMEI có trạng thái 'available' của biến thể này còn đủ không
                    $this->assertImeiAvailable($variant->id, $quantity, $product->name);

                    // Bước B: Tách mỗi số lượng thành một bản ghi OrderItem độc lập (quantity = 1, imei_id = null)
                    // Việc này giúp Admin khi duyệt đơn sẽ thấy đúng số lượng ô nhập tương ứng để điền IMEI thủ công vào.
                    for ($i = 0; $i < $quantity; $i++) {
                        OrderItem::query()->create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'price' => $price,
                            'quantity' => 1,
                            'total' => $price,
                            'imei_id' => null, // Để trống chờ Admin điền lúc đóng gói
                        ]);
                    }
                } else {
                    // Với hàng phụ kiện thông thường (quantity): Tạo gộp 1 dòng duy nhất và trừ trực tiếp stock_quantity
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'price' => $price,
                        'quantity' => $quantity,
                        'total' => $price * $quantity,
                        'imei_id' => null,
                    ]);

                    $this->decreaseVariantStock($variant, $quantity, $product->name);
                }

                // Ghi nhận lịch sử giao dịch xuất/tạm giữ kho
                InventoryTransaction::query()->create([
                    'product_variant_id' => $variant->id,
                    'type' => 'export',
                    'quantity' => $quantity,
                    'note' => 'Tạm giữ / xuất kho cho đơn hàng: ' . $order->order_code,
                ]);
            }

            // 6. Khởi tạo cổng thanh toán (Payment)
            Payment::query()->create([
                'order_id'         => $order->id,
                'payment_method'   => $data['payment_method'],
                'amount'           => $order->total_amount,
                'payment_status'   => 'pending',
                'transaction_code' => null,
                'paid_at'          => null,
                'expires_at'       => in_array($data['payment_method'], self::EXPIRING_METHODS, true)
                    ? now()->addMinutes(self::PAYMENT_EXPIRY_MINUTES)
                    : null,
            ]);

            // 7. Khấu trừ điểm thưởng của User nếu có sử dụng
            if ($pointsToUse > 0) {
                $this->pointService->deductPoints(
                    $user,
                    $pointsToUse,
                    'usage',
                    "Đổi điểm - Đơn hàng #{$order->order_code}"
                );
            }

            // 8. Tính điểm tích lũy dự kiến từ đơn hàng này
            $pointsEarned = $this->pointService->calculatePointsFromOrder($order->total_amount);

            if ($pointsEarned > 0) {
                $this->pointService->addPoints(
                    $user,
                    $pointsEarned,
                    'purchase',
                    "Mua hàng - Đơn hàng #{$order->order_code}"
                );
            }

            // 9. Dọn dẹp giỏ hàng đã thanh toán
            if ($itemIds === null) {
                $this->cartService->clear($user);
            } else {
                $this->cartService->removeItems($user, $itemIds);
            }

            return $order;
        });
    }

    /**
     * Kiểm tra số lượng IMEI khả dụng trong kho có đáp ứng đủ số lượng đặt mua hay không.
     */
    private function assertImeiAvailable(int $productVariantId, int $requestedQuantity, string $productName): void
    {
        $availableCount = Imei::query()
            ->where('product_variant_id', $productVariantId)
            ->where('status', 'available')
            ->count();

        if ($availableCount < $requestedQuantity) {
            throw ValidationException::withMessages([
                'inventory' => "Sản phẩm {$productName} không đủ số lượng máy (IMEI) khả dụng trong kho (Yêu cầu: {$requestedQuantity}, Còn lại: {$availableCount}).",
            ]);
        }
    }

    /**
     * Giảm số lượng tồn kho vật lý đối với phụ kiện (quantity).
     */
    private function decreaseVariantStock($variant, int $quantity, string $productName): void
    {
        $freshVariant = $variant->newQuery()
            ->whereKey($variant->id)
            ->lockForUpdate()
            ->first();

        if (! $freshVariant || $freshVariant->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'inventory' => 'Sản phẩm ' . $productName . ' không đủ tồn kho.',
            ]);
        }

        $freshVariant->decrement('stock_quantity', $quantity);
    }

    /**
     * Đánh dấu giao dịch hết hạn (quá thời gian giữ chỗ) và hoàn lại tồn kho/IMEI đã tạm giữ.
     */
    public function expirePayment(Payment $payment): void
    {
        if (! $payment->isExpired()) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $this->restoreInventory($payment->order);

            $payment->update([
                'payment_status' => 'failed',
                'payer_note'     => 'Giao dịch hết hạn do quá thời gian thanh toán.',
            ]);
        });
    }

    /**
     * Thử thanh toán lại: cấp lại tồn kho và mở phiên giao dịch mới.
     */
    public function retryPayment(Payment $payment): void
    {
        if ($payment->payment_status === 'paid' || ! in_array($payment->payment_method, self::EXPIRING_METHODS, true)) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $this->reallocateInventory($payment->order);

            $payment->update([
                'payment_status'   => 'pending',
                'transaction_code' => null,
                'payer_name'       => null,
                'payer_note'       => null,
                'expires_at'       => now()->addMinutes(self::PAYMENT_EXPIRY_MINUTES),
            ]);
        });
    }

    /**
     * Hoàn lại tồn kho phụ kiện nếu đơn hàng bị huỷ/quá hạn thanh toán.
     */
    private function restoreInventory(Order $order): void
    {
        foreach ($order->items()->with(['product', 'variant'])->get() as $item) {
            // Đối với hàng có IMEI thực tế, lúc này admin chưa gán imei_id hoặc nếu có gán nhưng huỷ thì giải phóng
            if ($item->imei_id) {
                $imei = Imei::find($item->imei_id);

                if ($imei && $imei->status === 'reserved') {
                    $imei->update([
                        'status'                    => 'available',
                        'reserved_at'                => null,
                        'reserved_by_order_item_id' => null,
                    ]);
                }
                $item->update(['imei_id' => null]);
            } 
            
            // Đối với phụ kiện thông thường thì hoàn lại stock_quantity
            if ($item->product?->product_type === 'quantity' && $item->variant) {
                $item->variant->increment('stock_quantity', $item->quantity);
            }
        }
    }

    /**
     * Tái kiểm tra và trừ kho khi khách bấm thực hiện lại thanh toán (Retry Payment).
     */
    private function reallocateInventory(Order $order): void
    {
        // Nhóm các items theo variant để kiểm tra tổng số lượng đặt mua
        $items = $order->items()->with(['product', 'variant'])->get();
        $grouped = $items->groupBy('product_variant_id');

        foreach ($grouped as $variantId => $orderItems) {
            $firstItem = $orderItems->first();
            $product = $firstItem->product;
            $totalQty = $orderItems->sum('quantity');

            if (! $product) {
                continue;
            }

            if ($product->product_type === 'imei/serial') {
                $this->assertImeiAvailable($variantId, $totalQty, $product->name);
            } elseif ($firstItem->variant) {
                $this->decreaseVariantStock($firstItem->variant, $totalQty, $product->name);
            }
        }
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'ORD' . now()->format('YmdHis') . strtoupper(Str::random(4));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }
}