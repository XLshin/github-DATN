<?php

namespace App\Services;

use App\Models\Cart;
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
            ->with('product')
            ->get();
    }

    public function addItem(User $user, Product $product, int $quantity = 1, ?int $variantId = null): void
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
            $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }
    }

    public function removeItem(User $user, int $productId): void
    {
        $this->getOrCreateCart($user)
            ->items()
            ->where('product_id', $productId)
            ->delete();
    }

    public function calculateTotal(Collection $items): float
    {
        return $items->sum(fn ($item) => (float) $item->product->price * $item->quantity);
    }

    public function clear(User $user): void
    {
        $this->getOrCreateCart($user)->items()->delete();
    }

    public function isEmpty(User $user): bool
    {
        return $this->getItems($user)->isEmpty();
    }
}
