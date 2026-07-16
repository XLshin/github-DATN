<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Imei;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * @param  array<int, int>|null  $itemIds  Nếu truyền, chỉ lấy đúng các dòng giỏ hàng có id trong danh sách này.
     */
    public function getItems(User $user, ?array $itemIds = null): Collection
    {
        return $this->getOrCreateCart($user)
            ->items()
            ->with([
                'product.images',
                'product.productGroup.images',
                'variant.images',
                'productVariant.images',
            ])
            ->latest('id')
            ->when($itemIds !== null, fn ($q) => $q->whereIn('id', $itemIds))
            ->get();
    }

    /**
     * Lấy các dòng giỏ hàng đã chọn theo id (dùng khi khách chỉ thanh toán một phần giỏ hàng).
     */
    public function getSelectedItems(User $user, array $cartItemIds): Collection
    {
        if (empty($cartItemIds)) {
            return collect();
        }

        return $this->getOrCreateCart($user)
            ->items()
            ->with([
                'product.images',
                'product.productGroup.images',
                'variant.images',
                'productVariant.images',
            ])
            ->whereIn('id', $cartItemIds)
            ->get();
    }

    public function addItem(User $user, Product $product, int $quantity = 1, ?int $variantId = null): CartItem
    {
        $cart = $this->getOrCreateCart($user);

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
            ->when(! $variantId, fn($q) => $q->whereNull('product_variant_id'))
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = $cart->items()->create([
                'product_id'         => $product->id,
                'product_variant_id' => $variantId,
                'quantity'           => $quantity,
            ]);
        }

        return $item->fresh(['product', 'variant']);
    }

    public function updateItemQuantity(User $user, int $cartItemId, int $quantity): ?CartItem
    {
        $item = $this->getOrCreateCart($user)->items()->whereKey($cartItemId)->first();

        if (! $item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh(['product', 'variant']);
    }

    /**
     * Xóa đúng 1 dòng theo cart_item_id (tránh xóa nhầm variant khác).
     */
    public function removeItem(User $user, int $cartItemId): void
    {
        $this->getOrCreateCart($user)
            ->items()
            ->where('id', $cartItemId)
            ->delete();
    }

    /**
     * Cập nhật số lượng; nếu quantity <= 0 thì xóa dòng đó.
     *
     * @return array{success: bool, message?: string, max_quantity?: int}
     */
    public function updateItem(User $user, int $cartItemId, int $quantity): array
    {
        $item = $this->getOrCreateCart($user)
            ->items()
            ->with(['product', 'productVariant'])
            ->where('id', $cartItemId)
            ->first();

        if (! $item) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.'];
        }

        if ($quantity <= 0) {
            $item->delete();
            return ['success' => true];
        }

        $available = $this->getAvailableStock($item->product, $item->productVariant);

        if ($quantity > $available) {
            return [
                'success'      => false,
                'message'      => $available > 0
                    ? "Sản phẩm {$item->product->name} chỉ còn {$available} sản phẩm trong kho."
                    : "Sản phẩm {$item->product->name} hiện đã hết hàng.",
                'max_quantity' => $available,
            ];
        }

        $item->update(['quantity' => $quantity]);

        return ['success' => true];
    }

    /**
     * Số lượng còn có thể bán của 1 variant, tùy loại sản phẩm.
     */
    public function getAvailableStock(Product $product, ?ProductVariant $variant): int
    {
        if (! $variant) {
            // Không có variant cụ thể → dùng tổng stock của product làm fallback
            return (int) $product->stock_quantity;
        }

        if ($product->product_type === 'imei/serial') {
            return Imei::query()
                ->where('product_variant_id', $variant->id)
                ->where('status', 'available')
                ->count();
        }

        return (int) $variant->stock_quantity;
    }

    public function calculateTotal(Collection $items): float
    {
        return $items->sum(fn (CartItem $item) => $this->unitPrice($item) * $item->quantity);
    }

    public function unitPrice(CartItem $item): float
    {
        $base = (float) ($item->product->price ?? 0);
        $extra = (float) ($item->variant->additional_price ?? $item->productVariant->additional_price ?? 0);

        return $base + $extra;
    }

    public function getCartCount(User $user): int
    {
        return $this->getCount($user);
    }

    public function clear(User $user): void
    {
        $this->getOrCreateCart($user)->items()->delete();
    }

    /**
     * Xóa đúng các dòng đã chọn (dùng sau khi checkout 1 phần giỏ hàng), giữ lại các dòng còn lại.
     *
     * @param  array<int, int>  $itemIds
     */
    public function removeItems(User $user, array $itemIds): void
    {
        $this->getOrCreateCart($user)
            ->items()
            ->whereIn('id', $itemIds)
            ->delete();
    }

    public function clearItems(User $user, array $cartItemIds): void
    {
        $this->removeItems($user, $cartItemIds);
    }

    public function isEmpty(User $user): bool
    {
        return $this->getItems($user)->isEmpty();
    }

    public function getCount(User $user): int
    {
        return (int) $this->getOrCreateCart($user)->items()->sum('quantity');
    }
}
