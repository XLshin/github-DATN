<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function getItems(User $user): Collection
    {
        return $this->getOrCreateCart($user)
            ->items()
            ->with(['product', 'variant'])
            ->latest('id')
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
            ->with(['product', 'variant'])
            ->whereIn('id', $cartItemIds)
            ->get();
    }

    public function addItem(User $user, Product $product, int $quantity = 1, ?int $variantId = null): CartItem
    {
        $cart = $this->getOrCreateCart($user);

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->when($variantId, fn ($q) => $q->where('product_variant_id', $variantId))
            ->when(! $variantId, fn ($q) => $q->whereNull('product_variant_id'))
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
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

    public function removeItem(User $user, int $cartItemId): void
    {
        $this->getOrCreateCart($user)->items()->whereKey($cartItemId)->delete();
    }

    public function calculateTotal(Collection $items): float
    {
        return $items->sum(fn (CartItem $item) => $this->unitPrice($item) * $item->quantity);
    }

    public function unitPrice(CartItem $item): float
    {
        $base = (float) ($item->product->price ?? 0);
        $extra = (float) ($item->variant->additional_price ?? 0);

        return $base + $extra;
    }

    public function getCartCount(User $user): int
    {
        return (int) $this->getOrCreateCart($user)->items()->sum('quantity');
    }

    public function clear(User $user): void
    {
        $this->getOrCreateCart($user)->items()->delete();
    }

    public function clearItems(User $user, array $cartItemIds): void
    {
        $this->getOrCreateCart($user)->items()->whereIn('id', $cartItemIds)->delete();
    }

    public function isEmpty(User $user): bool
    {
        return $this->getItems($user)->isEmpty();
    }
}
