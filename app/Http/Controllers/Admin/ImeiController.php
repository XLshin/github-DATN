<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Imei;
use App\Models\InventoryTransaction;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ImeiController extends Controller
{
    public function index(Request $request)
    {
        $query = Imei::with('productVariant.product.brand');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);

            $query->where(function ($query) use ($keyword) {
                $query->where('imei', 'like', "%{$keyword}%")
                    ->orWhereHas('productVariant.product', function ($productQuery) use ($keyword) {
                        $productQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('storage', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('productVariant', function ($variantQuery) use ($keyword) {
                        $variantQuery->where('color', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('variant_id')) {
            $query->where('product_variant_id', $request->variant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $imeis = $query
            ->latest()
            ->paginate(10);

        return view('admin.imeis.index', compact('imeis'));
    }

    public function create()
    {
        $imeiVariants = $this->imeiVariantQuery()->get();

        return view('admin.imeis.create', compact('imeiVariants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'imeis' => 'required|string',
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể sản phẩm.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'imeis.required' => 'Bạn phải nhập IMEI/Serial.',
        ]);

        $variant = ProductVariant::with('product')
            ->findOrFail($request->product_variant_id);

        if ($variant->product?->product_type !== 'imei/serial') {
            return back()
                ->withErrors([
                    'product_variant_id' => 'Sản phẩm này không quản lý bằng IMEI/Serial.',
                ])
                ->withInput();
        }

        $imeis = preg_split('/\r\n|\r|\n/', trim((string) $request->imeis));
        $imeis = array_filter(array_map('trim', $imeis));

        if (count($imeis) !== count(array_unique($imeis))) {
            return back()
                ->withErrors([
                    'imeis' => 'Danh sách IMEI/Serial bị trùng.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($imeis, $variant) {
            foreach ($imeis as $imei) {
                validator(
                    ['imei' => $imei],
                    ['imei' => 'required|digits:15|unique:imeis,imei'],
                    [
                        'imei.required' => 'IMEI không được để trống.',
                        'imei.digits' => 'IMEI phải gồm đúng 15 chữ số.',
                        'imei.unique' => "IMEI {$imei} đã tồn tại.",
                    ]
                )->validate();

                Imei::create([
                    'product_variant_id' => $variant->id,
                    'imei' => $imei,
                    'status' => 'available',
                ]);
            }

            InventoryTransaction::create([
                'product_variant_id' => $variant->id,
                'type' => 'import',
                'quantity' => count($imeis),
                'note' => 'Nhập kho bằng IMEI/Serial',
            ]);
        });

        return redirect()
            ->route('admin.stocks')
            ->with('success', 'Đã nhập ' . count($imeis) . ' IMEI thành công.');
    }

    public function show(string $id)
    {
        $imei = Imei::with([
            'productVariant.product.brand',
            'orderItem.order.user',
            'warranty',
        ])->findOrFail($id);

        return view('admin.imeis.show', compact('imei'));
    }

    public function edit(string $id)
    {
        $imei = Imei::with('productVariant.product.brand', 'orderItem.order', 'warranty')
            ->findOrFail($id);

        $imeiVariants = $this->imeiVariantQuery()->get();
        $canAdjust = $imei->status === 'available'
            && !$imei->reserved_by_order_item_id
            && !$imei->warranty;

        return view('admin.imeis.edit', compact('imei', 'imeiVariants', 'canAdjust'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'imei' => [
                'required',
                'digits:15',
                Rule::unique('imeis', 'imei')->ignore($id),
            ],
            'status' => ['required', Rule::in(['available', 'returned'])],
            'note' => 'required|string|max:1000',
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể sản phẩm.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'imei.required' => 'IMEI không được để trống.',
            'imei.digits' => 'IMEI phải gồm đúng 15 chữ số.',
            'imei.unique' => 'IMEI đã tồn tại trong hệ thống.',
            'status.required' => 'Bạn phải chọn trạng thái điều chỉnh.',
            'status.in' => 'Trạng thái điều chỉnh không hợp lệ.',
            'note.required' => 'Bạn phải nhập lý do điều chỉnh IMEI.',
            'note.max' => 'Lý do điều chỉnh không được vượt quá 1000 ký tự.',
        ]);

        $imei = DB::transaction(function () use ($id, $validated) {
            $imei = Imei::with('productVariant.product', 'warranty')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $imei->status !== 'available'
                || $imei->reserved_by_order_item_id
                || $imei->warranty
            ) {
                throw ValidationException::withMessages([
                    'imei' => 'Không thể điều chỉnh IMEI đã giữ chỗ, đã bán hoặc đã có bảo hành.',
                ]);
            }

            $newVariant = ProductVariant::with('product')->findOrFail($validated['product_variant_id']);

            if ($newVariant->product?->product_type !== 'imei/serial') {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'Chỉ được chuyển IMEI sang biến thể quản lý bằng IMEI/Serial.',
                ]);
            }

            $oldImei = $imei->imei;
            $oldVariantId = $imei->product_variant_id;
            $oldStatus = $imei->status;

            $imei->update([
                'product_variant_id' => $newVariant->id,
                'imei' => $validated['imei'],
                'status' => $validated['status'],
                'reserved_at' => null,
                'reserved_by_order_item_id' => null,
            ]);

            $changes = [];

            if ($oldImei !== $imei->imei) {
                $changes[] = "sửa mã {$oldImei} thành {$imei->imei}";
            }

            if ((int) $oldVariantId !== (int) $imei->product_variant_id) {
                $changes[] = "chuyển biến thể từ #{$oldVariantId} sang #{$imei->product_variant_id}";
            }

            if ($oldStatus !== $imei->status) {
                $changes[] = "đổi trạng thái từ {$oldStatus} sang {$imei->status}";
            }

            $quantity = $imei->status === 'returned' ? -1 : 0;
            $note = 'Điều chỉnh IMEI: '
                . ($changes ? implode(', ', $changes) : 'cập nhật ghi chú')
                . '. Lý do: '
                . $validated['note'];

            InventoryTransaction::create([
                'product_variant_id' => $oldVariantId,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'note' => $note,
            ]);

            return $imei;
        });

        return redirect()
            ->route('admin.imeis.show', $imei->id)
            ->with('success', 'Đã điều chỉnh IMEI thành công.');
    }

    public function destroy(string $id)
    {
        return back()->with('error', 'Không xóa cứng IMEI. Hãy dùng điều chỉnh IMEI và chuyển trạng thái thành nhập nhầm/loại khỏi kho.');
    }

    public function stock(Request $request)
    {
        $keyword = trim((string) $request->keyword);
        $isPartialImeiSearch = preg_match('/^\d{4,15}$/', $keyword) === 1;
        $matchedImeis = collect();

        if ($request->filled('keyword')) {
            $imei = Imei::where('imei', $keyword)->first();

            if ($imei) {
                return redirect()->route('admin.imeis.show', $imei->id);
            }

            if ($isPartialImeiSearch) {
                $matchedImeis = Imei::with([
                    'productVariant.product.brand',
                ])
                    ->where('imei', 'like', "%{$keyword}%")
                    ->orderBy('imei')
                    ->limit(30)
                    ->get();
            }
        }

        $query = ProductVariant::with([
            'product.brand',
            'imeis',
        ])
            ->whereHas('imeis')
            ->whereHas('product', function ($productQuery) {
                $productQuery->where('product_type', 'imei/serial');
            });

        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword, $isPartialImeiSearch) {
                if ($isPartialImeiSearch) {
                    $query->whereHas('imeis', function ($q) use ($keyword) {
                        $q->where('imei', 'like', "%{$keyword}%");
                    });

                    return;
                }

                $query->whereHas('product', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('storage', 'like', "%{$keyword}%");
                })
                    ->orWhere('color', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        $stocks = $query->get()->map(function ($variant) {
            $variant->available_count = $variant->imeis->where('status', 'available')->count();
            $variant->sold_count = $variant->imeis->where('status', 'sold')->count();
            $variant->warranty_count = $variant->imeis->where('status', 'warranty')->count();
            $variant->reserved_count = $variant->imeis->where('status', 'reserved')->count();

            return $variant;
        });

        $brands = Brand::orderBy('name')->get();

        return view(
            'admin.inventory.stocks',
            compact('stocks', 'brands', 'matchedImeis', 'keyword', 'isPartialImeiSearch')
        );
    }

    public function accessoryStock(Request $request)
    {
        $query = ProductVariant::with([
            'product.brand',
            'product.category',
        ])->whereHas('product', function ($query) {
            $query->where('product_type', 'quantity');
        });

        if ($request->filled('search')) {
            $keyword = trim((string) $request->search);

            $query->where(function ($q) use ($keyword) {
                $q->whereHas('product', function ($productQuery) use ($keyword) {
                    $productQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('storage', 'like', "%{$keyword}%");
                })
                    ->orWhere('color', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        $stocks = $query->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.inventory.accessories', compact('stocks', 'brands'));
    }

    private function imeiVariantQuery()
    {
        return ProductVariant::with('product')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'imei/serial');
            })
            ->orderBy('product_id')
            ->orderBy('color');
    }
}
