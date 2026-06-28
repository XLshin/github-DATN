<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Ghi một bản ghi vào bảng inventory_transactions.
     * Hỗ trợ cả schema cũ (cột 'quantity') và schema mới (cột 'change_qty').
     */
    private function logTransaction(?int $variantId, int $changeQty, string $type, ?string $refType, ?int $refId, ?int $performedBy): void
    {
        if (Schema::hasColumn('inventory_transactions', 'change_qty')) {
            $row = [
                'product_variant_id' => $variantId,
                'change_qty'         => $changeQty,
                'type'               => $type,
                'reference_type'     => $refType,
                'reference_id'       => $refId,
                'performed_by'       => $performedBy,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
            // Nếu cột 'quantity' cũ vẫn còn (NOT NULL) thì cung cấp giá trị để tránh constraint violation
            if (Schema::hasColumn('inventory_transactions', 'quantity')) {
                $row['quantity'] = $changeQty;
            }
            DB::table('inventory_transactions')->insert($row);
        } elseif (Schema::hasColumn('inventory_transactions', 'quantity')) {
            DB::table('inventory_transactions')->insert([
                'product_variant_id' => $variantId,
                'quantity'           => $changeQty,
                'type'               => in_array($type, ['import', 'export', 'return', 'adjustment'])
                    ? $type : 'adjustment',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    /**
     * Đặt trước (reserve) số lượng cho một biến thể sản phẩm.
     */
    public function reserveVariant(int $variantId, int $qty, ?string $referenceType = null, ?int $referenceId = null, ?int $performedBy = null): bool
    {
        return DB::transaction(function () use ($variantId, $qty, $referenceType, $referenceId, $performedBy) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (! $variant) {
                return false;
            }

            $available = ($variant->stock_quantity ?? 0) - ($variant->reserved_quantity ?? 0);
            if ($available < $qty) {
                return false;
            }

            $variant->reserved_quantity = ($variant->reserved_quantity ?? 0) + $qty;
            $variant->save();

            $this->logTransaction($variantId, $qty, 'reserved', $referenceType, $referenceId, $performedBy);

            return true;
        });
    }

    public function reserveProduct(int $productId, int $qty, ?string $referenceType = null, ?int $referenceId = null, ?int $performedBy = null): bool
    {
        return DB::transaction(function () use ($productId, $qty, $referenceType, $referenceId, $performedBy) {
            $product = Product::lockForUpdate()->find($productId);
            if (! $product) {
                return false;
            }

            $stock    = $product->stock_quantity ?? 0;
            $reserved = Schema::hasColumn('products', 'reserved_quantity') ? ($product->reserved_quantity ?? 0) : 0;
            if (($stock - $reserved) < $qty) {
                return false;
            }

            if (Schema::hasColumn('products', 'reserved_quantity')) {
                $product->reserved_quantity = $reserved + $qty;
                $product->save();
            }

            $this->logTransaction(null, $qty, 'reserved', $referenceType, $referenceId, $performedBy);

            return true;
        });
    }

    public function releaseVariant(int $variantId, int $qty, ?string $referenceType = null, ?int $referenceId = null, ?int $performedBy = null): bool
    {
        return DB::transaction(function () use ($variantId, $qty, $referenceType, $referenceId, $performedBy) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (! $variant) {
                return false;
            }

            $variant->reserved_quantity = max(0, ($variant->reserved_quantity ?? 0) - $qty);
            $variant->save();

            $this->logTransaction($variantId, -$qty, 'release', $referenceType, $referenceId, $performedBy);

            return true;
        });
    }

    public function releaseProduct(int $productId, int $qty, ?string $referenceType = null, ?int $referenceId = null, ?int $performedBy = null): bool
    {
        return DB::transaction(function () use ($productId, $qty, $referenceType, $referenceId, $performedBy) {
            $product = Product::lockForUpdate()->find($productId);
            if (! $product) {
                return false;
            }

            if (Schema::hasColumn('products', 'reserved_quantity')) {
                $product->reserved_quantity = max(0, ($product->reserved_quantity ?? 0) - $qty);
                $product->save();
            }

            $this->logTransaction(null, -$qty, 'release', $referenceType, $referenceId, $performedBy);

            return true;
        });
    }

    public function commitVariant(int $variantId, int $qty, ?string $referenceType = null, ?int $referenceId = null, ?int $performedBy = null): bool
    {
        return DB::transaction(function () use ($variantId, $qty, $referenceType, $referenceId, $performedBy) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);
            if (! $variant) {
                return false;
            }

            $variant->stock_quantity    = max(0, ($variant->stock_quantity ?? 0) - $qty);
            $variant->reserved_quantity = max(0, ($variant->reserved_quantity ?? 0) - $qty);
            $variant->save();

            $this->logTransaction($variantId, -$qty, 'commit', $referenceType, $referenceId, $performedBy);

            return true;
        });
    }

    public function commitProduct(int $productId, int $qty, ?string $referenceType = null, ?int $referenceId = null, ?int $performedBy = null): bool
    {
        return DB::transaction(function () use ($productId, $qty, $referenceType, $referenceId, $performedBy) {
            $product = Product::lockForUpdate()->find($productId);
            if (! $product) {
                return false;
            }

            if (Schema::hasColumn('products', 'stock_quantity')) {
                $product->stock_quantity = max(0, ($product->stock_quantity ?? 0) - $qty);
            }
            if (Schema::hasColumn('products', 'reserved_quantity')) {
                $product->reserved_quantity = max(0, ($product->reserved_quantity ?? 0) - $qty);
            }
            $product->save();

            $this->logTransaction(null, -$qty, 'commit', $referenceType, $referenceId, $performedBy);

            return true;
        });
    }
}
