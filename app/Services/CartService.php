<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private string $sessionKey = 'cart_items';

    // --- CÁC HÀM XỬ LÝ CHUNG ---

    public function getItems(?User $user = null)
    {
        if ($user) {
            return $this->getDbItems($user);
        }
        return $this->getSessionItems();
    }

    public function calculateTotal($items): float
    {
        // Tự động nhận diện Collection (DB) hay Array (Session)
        if ($items instanceof Collection) {
            return $items->sum(function ($item) {
                $basePrice = $item->product->price ?? 0;
                $additionalPrice = $item->variant->additional_price ?? 0;
                return (float) ($basePrice + $additionalPrice) * $item->quantity;
            });
        }

        $total = 0.0;
        foreach ($items as $it) {
            $price = $it['price'] ?? 0;
            $qty = $it['quantity'] ?? 0;
            $total += ((float) $price) * ((int) $qty);
        }
        return $total;
    }

    // --- LOGIC CHO DATABASE (KHI ĐÃ ĐĂNG NHẬP) ---

    private function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    private function getDbItems(User $user): Collection
    {
        return $this->getOrCreateCart($user)
            ->items()
            ->with(['product', 'variant'])
            ->get();
    }

    public function addItem(User $user, Product $product, int $quantity = 1, ?int $variantId = null): void
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
            $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }
    }

    public function removeItem(User $user, int $cartItemId): void
    {
        $this->getOrCreateCart($user)
            ->items()
            ->where('id', $cartItemId)
            ->delete();
    }

    // --- LOGIC CHO SESSION (KHI CHƯA ĐĂNG NHẬP) ---

    public function all(): array
    {
        return Session::get($this->sessionKey, []);
    }

    private function getSessionItems(): array
    {
        $items = $this->all();
        if (empty($items)) return [];

        $result = [];
        $productIds = collect($items)->whereNull('variant_id')->pluck('product_id')->unique();
        $variantIds = collect($items)->whereNotNull('variant_id')->pluck('variant_id')->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::with('product')->whereIn('id', $variantIds)->get()->keyBy('id');

        foreach ($items as $k => $it) {
            $row = $it;
            $row['key'] = $k;

            if (!empty($it['variant_id'])) {
                $variant = $variants->get($it['variant_id']);
                $row['variant'] = $variant;
                $row['product'] = $variant?->product;
                $row['price'] = $variant ? (($variant->product->price ?? 0) + ($variant->additional_price ?? 0)) : 0;
            } else {
                $product = $products->get($it['product_id']);
                $row['product'] = $product;
                $row['price'] = $product?->price ?? 0;
            }
            $result[$k] = $row;
        }

        return $result;
    }

    public function add(int $productId, ?int $variantId = null, int $qty = 1, array $meta = []): void
    {
        $items = $this->all();
        $id = $variantId ? "v{$variantId}" : "p{$productId}";

        if (isset($items[$id])) {
            $items[$id]['quantity'] += $qty;
        } else {
            $items[$id] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $qty,
                'meta' => $meta,
            ];
        }

        Session::put($this->sessionKey, $items);
    }

    public function update(string $itemKey, int $qty): void
    {
        $items = $this->all();
        if (isset($items[$itemKey])) {
            if ($qty <= 0) {
                unset($items[$itemKey]);
            } else {
                $items[$itemKey]['quantity'] = $qty;
            }
            Session::put($this->sessionKey, $items);
        }
    }

    public function remove(string $itemKey): void
    {
        $items = $this->all();
        if (isset($items[$itemKey])) {
            unset($items[$itemKey]);
            Session::put($this->sessionKey, $items);
        }
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }
}
