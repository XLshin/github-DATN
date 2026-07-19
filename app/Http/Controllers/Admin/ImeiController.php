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
            'imeis' => 'nullable|required_without:imei_file|string',
            'imei_file' => 'nullable|required_without:imeis|file|mimes:xlsx,csv,txt|max:2048',
        ], [
            'product_variant_id.required' => 'Bạn phải chọn biến thể sản phẩm.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'imeis.required_without' => 'Bạn phải nhập IMEI/Serial hoặc upload file.',
            'imei_file.required_without' => 'Bạn phải nhập IMEI/Serial hoặc upload file.',
            'imei_file.file' => 'File IMEI không hợp lệ.',
            'imei_file.mimes' => 'File IMEI chỉ hỗ trợ định dạng xlsx, csv hoặc txt.',
            'imei_file.max' => 'File IMEI không được vượt quá 2MB.',
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

        try {
            $imeis = $this->collectImeisFromRequest($request);
        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors(['imei_file' => $exception->getMessage()])
                ->withInput();
        }

        if (empty($imeis)) {
            return back()
                ->withErrors(['imeis' => 'Danh sách IMEI/Serial không được để trống.'])
                ->withInput();
        }

        if (count($imeis) !== count(array_unique($imeis))) {
            return back()
                ->withErrors([
                    'imeis' => 'Danh sách IMEI/Serial bị trùng.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($imeis, $variant, $request) {
            foreach ($imeis as $imei) {
                validator(
                    ['imei' => $imei],
                    ['imei' => 'required|digits:15|unique:imeis,imei'],
                    [
                        'imei.required' => 'IMEI không được để trống.',
                        'imei.digits' => "IMEI {$imei} phải gồm đúng 15 chữ số.",
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
                'note' => $request->hasFile('imei_file')
                    ? 'Nhập kho IMEI/Serial từ file ' . $request->file('imei_file')->getClientOriginalName()
                    : 'Nhập kho bằng IMEI/Serial',
            ]);
        });

        // Cập nhật stock_quantity theo số IMEI available thực tế.
        $variant->update([
            'stock_quantity' => $variant->imeis()->where('status', 'available')->count(),
        ]);

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

    public function createBulkTransfer()
    {
        $imeiVariants = $this->imeiVariantQuery()->get();

        return view('admin.imeis.bulk-transfer', compact('imeiVariants'));
    }

    public function storeBulkTransfer(Request $request)
    {
        $validated = $request->validate([
            'target_product_variant_id' => 'required|exists:product_variants,id',
            'imeis' => 'nullable|required_without:imei_file|string',
            'imei_file' => 'nullable|required_without:imeis|file|mimes:xlsx,csv,txt|max:2048',
            'note' => 'required|string|max:1000',
        ], [
            'target_product_variant_id.required' => 'Bạn phải chọn biến thể đích.',
            'target_product_variant_id.exists' => 'Biến thể đích không tồn tại.',
            'imeis.required_without' => 'Bạn phải nhập danh sách IMEI cần chuyển hoặc upload file.',
            'imei_file.required_without' => 'Bạn phải nhập danh sách IMEI cần chuyển hoặc upload file.',
            'imei_file.file' => 'File IMEI không hợp lệ.',
            'imei_file.mimes' => 'File IMEI chỉ hỗ trợ định dạng xlsx, csv hoặc txt.',
            'imei_file.max' => 'File IMEI không được vượt quá 2MB.',
            'note.required' => 'Bạn phải nhập lý do chuyển IMEI.',
            'note.max' => 'Lý do chuyển IMEI không được vượt quá 1000 ký tự.',
        ]);

        try {
            $imeis = $this->collectImeisFromRequest($request);
        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors(['imei_file' => $exception->getMessage()])
                ->withInput();
        }

        if (empty($imeis)) {
            return back()
                ->withErrors(['imeis' => 'Danh sách IMEI cần chuyển không được để trống.'])
                ->withInput();
        }

        if (count($imeis) !== count(array_unique($imeis))) {
            return back()
                ->withErrors(['imeis' => 'Danh sách IMEI cần chuyển bị trùng.'])
                ->withInput();
        }

        foreach ($imeis as $imei) {
            validator(
                ['imei' => $imei],
                ['imei' => 'required|digits:15'],
                [
                    'imei.required' => 'IMEI không được để trống.',
                    'imei.digits' => "IMEI {$imei} phải gồm đúng 15 chữ số.",
                ]
            )->validate();
        }

        $targetVariant = ProductVariant::with('product')
            ->findOrFail($validated['target_product_variant_id']);

        if ($targetVariant->product?->product_type !== 'imei/serial') {
            return back()
                ->withErrors(['target_product_variant_id' => 'Chỉ được chuyển sang biến thể quản lý bằng IMEI/Serial.'])
                ->withInput();
        }

        DB::transaction(function () use ($imeis, $targetVariant, $validated) {
            $imeiModels = Imei::with('warranty')
                ->whereIn('imei', $imeis)
                ->lockForUpdate()
                ->get();

            $foundImeis = $imeiModels->pluck('imei')->all();
            $missingImeis = array_values(array_diff($imeis, $foundImeis));

            if (!empty($missingImeis)) {
                throw ValidationException::withMessages([
                    'imeis' => 'Không tìm thấy IMEI: ' . implode(', ', array_slice($missingImeis, 0, 10))
                        . (count($missingImeis) > 10 ? '...' : ''),
                ]);
            }

            $lockedImeis = $imeiModels->filter(function (Imei $imei) {
                return $imei->status !== 'available'
                    || $imei->reserved_by_order_item_id
                    || $imei->warranty;
            });

            if ($lockedImeis->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'imeis' => 'Chỉ được chuyển IMEI còn hàng. Các mã không thể chuyển: '
                        . $lockedImeis->pluck('imei')->take(10)->implode(', ')
                        . ($lockedImeis->count() > 10 ? '...' : ''),
                ]);
            }

            $sameTargetImeis = $imeiModels
                ->where('product_variant_id', $targetVariant->id)
                ->pluck('imei');

            if ($sameTargetImeis->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'imeis' => 'Một số IMEI đã nằm trong biến thể đích: '
                        . $sameTargetImeis->take(10)->implode(', ')
                        . ($sameTargetImeis->count() > 10 ? '...' : ''),
                ]);
            }

            $sourceCounts = $imeiModels
                ->groupBy('product_variant_id')
                ->map(fn ($items) => $items->count());

            Imei::whereIn('id', $imeiModels->pluck('id'))
                ->update([
                    'product_variant_id' => $targetVariant->id,
                    'updated_at' => now(),
                ]);

            foreach ($sourceCounts as $sourceVariantId => $quantity) {
                InventoryTransaction::create([
                    'product_variant_id' => $sourceVariantId,
                    'type' => 'adjustment',
                    'quantity' => -$quantity,
                    'note' => "Chuyển {$quantity} IMEI sang biến thể #{$targetVariant->id}. Lý do: {$validated['note']}",
                ]);
            }

            InventoryTransaction::create([
                'product_variant_id' => $targetVariant->id,
                'type' => 'adjustment',
                'quantity' => $imeiModels->count(),
                'note' => "Nhận {$imeiModels->count()} IMEI chuyển hàng loạt. Lý do: {$validated['note']}",
            ]);

            $affectedVariantIds = $sourceCounts->keys()
                ->push($targetVariant->id)
                ->unique()
                ->values();

            ProductVariant::whereIn('id', $affectedVariantIds)
                ->get()
                ->each(function (ProductVariant $variant) {
                    $variant->update([
                        'stock_quantity' => $variant->imeis()->where('status', 'available')->count(),
                    ]);
                });
        });

        return redirect()
            ->route('admin.stocks')
            ->with('success', 'Đã chuyển ' . count($imeis) . ' IMEI sang biến thể mới.');
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

    private function collectImeisFromRequest(Request $request): array
    {
        $imeis = $this->parseTextImeis((string) $request->input('imeis', ''));

        if ($request->hasFile('imei_file')) {
            $file = $request->file('imei_file');
            $extension = strtolower($file->getClientOriginalExtension());

            $fileImeis = match ($extension) {
                'xlsx' => $this->parseXlsxImeis($file->getRealPath()),
                'csv', 'txt' => $this->parseDelimitedImeis($file->getRealPath()),
                default => [],
            };

            $imeis = array_merge($imeis, $fileImeis);
        }

        return array_values(array_filter(array_map([$this, 'normalizeImei'], $imeis)));
    }

    private function parseTextImeis(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        return preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
    }

    private function parseDelimitedImeis(string $path): array
    {
        $rows = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$rows) {
            return [];
        }

        return array_map(function (string $row) {
            $columns = str_getcsv($row);

            return $columns[0] ?? '';
        }, $rows);
    }

    private function parseXlsxImeis(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Máy chủ chưa bật ZipArchive nên chưa đọc được file xlsx. Bạn có thể dùng csv/txt hoặc bật extension zip.');
        }

        if (!function_exists('simplexml_load_string')) {
            throw new \RuntimeException('Máy chủ chưa bật SimpleXML nên chưa đọc được file xlsx.');
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Không thể mở file Excel. Vui lòng kiểm tra lại file.');
        }

        $sharedStrings = $this->readXlsxSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('File Excel chưa có sheet đầu tiên để đọc IMEI.');
        }

        $sheet = simplexml_load_string($sheetXml);

        if (!$sheet) {
            throw new \RuntimeException('Không thể đọc dữ liệu trong sheet Excel.');
        }

        $imeis = [];

        foreach ($sheet->sheetData->row ?? [] as $row) {
            $firstCell = $row->c[0] ?? null;

            if (!$firstCell) {
                continue;
            }

            $value = (string) ($firstCell->v ?? '');
            $type = (string) ($firstCell['t'] ?? '');

            if ($type === 's') {
                $value = $sharedStrings[(int) $value] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string) ($firstCell->is->t ?? '');
            }

            $imeis[] = $value;
        }

        return $imeis;
    }

    private function readXlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $sharedStringXml = simplexml_load_string($xml);

        if (!$sharedStringXml) {
            return [];
        }

        $strings = [];

        foreach ($sharedStringXml->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';

            foreach ($item->r as $run) {
                $text .= (string) ($run->t ?? '');
            }

            $strings[] = $text;
        }

        return $strings;
    }

    private function normalizeImei(string $imei): string
    {
        $imei = trim($imei);
        $imei = preg_replace('/^\xEF\xBB\xBF/', '', $imei) ?? $imei;
        $imei = trim($imei, " \t\n\r\0\x0B'\"");

        if (strtolower($imei) === 'imei' || strtolower($imei) === 'serial') {
            return '';
        }

        return $imei;
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
