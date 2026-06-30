<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Session\Store;
use App\Models\Product;
use App\Models\ProductVariant;

class CartService
{
    private Store $session;
    private string $key = 'cart_items';

    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    public function all(): array
    {
        return $this->session->get($this->key, []);
    }

    public function getItems($user = null): array
    {
        $items = $this->all();
        $result = [];

        foreach ($items as $k => $it) {
            $row = $it;
            $row['key'] = $k;
            if (!empty($it['variant_id'])) {
                $variant = ProductVariant::with('product')->find($it['variant_id']);
                $row['variant'] = $variant;
                $row['product'] = $variant?->product;
                $row['price'] = $variant ? ($variant->product->price + ($variant->additional_price ?? 0)) : 0;
            } else {
                $product = Product::find($it['product_id']);
                $row['product'] = $product;
                $row['price'] = $product?->price ?? 0;
            }
            $result[$k] = $row;
        }

        return $result;
    }

    public function calculateTotal(array $items): float
    {
        $total = 0.0;
        foreach ($items as $it) {
            $price = $it['price'] ?? 0;
            $qty = $it['quantity'] ?? 0;
            $total += ((float) $price) * ((int) $qty);
        }
        return $total;
    }

    public function isEmpty($user = null): bool
    {
        return empty($this->all());
    }

    public function add(int $productId, int $variantId = null, int $qty = 1, array $meta = []): void
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

        $this->session->put($this->key, $items);
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
            $this->session->put($this->key, $items);
        }
    }

    public function remove(string $itemKey): void
    {
        $items = $this->all();
        if (isset($items[$itemKey])) {
            unset($items[$itemKey]);
            $this->session->put($this->key, $items);
        }
    }

    public function clear(): void
    {
        $this->session->forget($this->key);
    }

    public function count(): int
    {
        return array_sum(array_map(fn($i) => $i['quantity'] ?? 0, $this->all()));
    }
}
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
