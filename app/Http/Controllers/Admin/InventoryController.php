<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with([
            'productVariant.product.brand',
            'user',
        ]);

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);

            $query->where(function ($query) use ($keyword) {
                $query->whereHas('productVariant.product', function ($productQuery) use ($keyword) {
                    $productQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('storage', 'like', "%{$keyword}%")
                        ->orWhereHas('brand', function ($brandQuery) use ($keyword) {
                            $brandQuery->where('name', 'like', "%{$keyword}%");
                        });
                })
                    ->orWhereHas('productVariant', function ($variantQuery) use ($keyword) {
                        $variantQuery->where('color', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    })
                    ->orWhere('note', 'like', "%{$keyword}%");

                if (is_numeric($keyword)) {
                    $query->orWhere('product_variant_id', (int) $keyword);
                }
            });
        }

        if ($request->filled('transaction_type')) {
            $query->where('type', $request->transaction_type);
        }

        $transactions = $query
            ->latest()
            ->paginate(10);

        return view('admin.inventory.index', compact('transactions'));
    }

    public function create()
    {
        $quantityVariants = $this->quantityVariantQuery()->get();

        return view('admin.inventory.create', compact('quantityVariants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:1000',
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể phụ kiện.',
            'product_variant_id.exists' => 'Biến thể phụ kiện không tồn tại.',
            'quantity.required' => 'Bạn phải nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        DB::transaction(function () use ($validated) {
            $variant = $this->lockedQuantityVariant((int) $validated['product_variant_id']);
            $quantity = (int) $validated['quantity'];

            $variant->increment('stock_quantity', $quantity);

            InventoryTransaction::create([
                'product_variant_id' => $variant->id,
                'type' => 'import',
                'quantity' => $quantity,
                'note' => ($validated['note'] ?? null) ?: 'Nhập kho phụ kiện',
            ]);
        });

        return redirect()
            ->route('admin.stocks.accessories')
            ->with('success', 'Đã nhập kho phụ kiện thành công.');
    }

    public function createAdjustment(Request $request)
    {
        $quantityVariants = $this->quantityVariantQuery()->get();
        $selectedVariantId = $request->integer('product_variant_id') ?: null;

        return view('admin.inventory.adjust', compact('quantityVariants', 'selectedVariantId'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|not_in:0',
            'note' => 'required|string|max:1000',
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể cần điều chỉnh.',
            'product_variant_id.exists' => 'Biến thể không tồn tại.',
            'quantity.required' => 'Bạn phải nhập số lượng điều chỉnh.',
            'quantity.integer' => 'Số lượng điều chỉnh phải là số nguyên.',
            'quantity.not_in' => 'Số lượng điều chỉnh phải khác 0.',
            'note.required' => 'Bạn phải nhập lý do điều chỉnh kho.',
            'note.max' => 'Lý do điều chỉnh không được vượt quá 1000 ký tự.',
        ]);

        DB::transaction(function () use ($validated) {
            $variant = $this->lockedQuantityVariant((int) $validated['product_variant_id']);
            $delta = (int) $validated['quantity'];
            $newStock = (int) $variant->stock_quantity + $delta;

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Số lượng điều chỉnh làm tồn kho bị âm. Tồn hiện tại là ' . $variant->stock_quantity . '.',
                ]);
            }

            $variant->update([
                'stock_quantity' => $newStock,
            ]);

            InventoryTransaction::create([
                'product_variant_id' => $variant->id,
                'type' => 'adjustment',
                'quantity' => $delta,
                'note' => $validated['note'],
            ]);
        });

        return redirect()
            ->route('admin.stocks.accessories')
            ->with('success', 'Đã điều chỉnh kho phụ kiện thành công.');
    }

    private function quantityVariantQuery()
    {
        return ProductVariant::with('product')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'quantity');
            })
            ->orderBy('product_id')
            ->orderBy('color');
    }

    private function lockedQuantityVariant(int $variantId): ProductVariant
    {
        $variant = ProductVariant::with('product')
            ->whereKey($variantId)
            ->lockForUpdate()
            ->firstOrFail();

        if (($variant->product?->product_type ?? null) !== 'quantity') {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Chỉ được thao tác số lượng với sản phẩm quản lý theo số lượng.',
            ]);
        }

        return $variant;
    }
}
