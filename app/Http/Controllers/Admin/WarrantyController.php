<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Imei;
use App\Models\Warranty;
use App\Models\WarrantyMedia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarrantyController extends Controller
{
    private const OPEN_STATUSES = ['claimed'];

    private const ADMIN_ALLOWED_STATUSES = ['claimed', 'active'];

    public function index(Request $request)
    {
        $warranties = Warranty::with(['imei', 'order'])
            ->withCount(['receptionMedia', 'completionMedia'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim($request->keyword);

                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('imei', function ($q) use ($keyword) {
                        $q->where('imei', 'like', "%{$keyword}%");
                    })->orWhereHas('order', function ($q) use ($keyword) {
                        $q->where('order_code', 'like', "%{$keyword}%")
                            ->orWhere('customer_name', 'like', "%{$keyword}%")
                            ->orWhere('customer_phone', 'like', "%{$keyword}%");
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $warrantyDetails = $this->warrantyDetailMap($warranties->pluck('id')->all());

        return view('admin.warranties.index', compact('warranties', 'warrantyDetails'));
    }

    public function create(Request $request)
    {
        $soldImeis = $this->soldImeiQuery($request->keyword)
            ->orderByDesc('i.updated_at')
            ->orderByDesc('i.id')
            ->paginate(10)
            ->withQueryString();

        $selectedImei = null;

        if ($request->filled('imei_id')) {
            $selectedImei = $this->soldImeiQuery()
                ->where('i.id', $request->integer('imei_id'))
                ->first();

            if (!$selectedImei) {
                return redirect()
                    ->route('admin.warranties.create')
                    ->with('error', 'Không tìm thấy IMEI đã bán hoặc IMEI này không đủ điều kiện tạo phiếu bảo hành.');
            }

            if ($selectedImei->open_warranty_id) {
                return redirect()
                    ->route('admin.warranties.create')
                    ->with('error', 'IMEI này đang có phiếu bảo hành chưa hoàn tất. Vui lòng xử lý xong phiếu hiện tại trước khi tạo phiếu mới.');
            }

            if ($this->isWarrantyPeriodExpired($selectedImei)) {
                return redirect()
                    ->route('admin.warranties.create')
                    ->with('error', 'IMEI này đã quá thời hạn bảo hành, không thể tạo phiếu bảo hành mới.');
            }
        }

        return view('admin.warranties.create', compact('soldImeis', 'selectedImei'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'imei_id' => ['required', 'integer', 'exists:imeis,id'],
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date', 'after_or_equal:warranty_start'],
            'status' => ['required', Rule::in(['claimed'])],
            'customer_note' => ['required', 'string', 'max:2000'],

            'reception_media' => ['required', 'array', 'min:1', 'max:12'],
            'reception_media.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm,mkv',
                'max:102400',
            ],
        ], [
            'customer_note.required' => 'Vui lòng nhập lỗi khách báo hoặc ghi chú tiếp nhận.',
            'reception_media.required' => 'Vui lòng upload ít nhất 1 ảnh hoặc video tình trạng máy lúc tiếp nhận.',
            'reception_media.min' => 'Vui lòng upload ít nhất 1 ảnh hoặc video tình trạng máy lúc tiếp nhận.',
            'reception_media.*.mimes' => 'File tiếp nhận chỉ được là ảnh jpg, jpeg, png, webp hoặc video mp4, mov, avi, webm, mkv.',
            'reception_media.*.max' => 'Mỗi file tiếp nhận không được vượt quá 100MB.',
        ]);

        $imeiDetail = $this->soldImeiQuery()
            ->where('i.id', $validated['imei_id'])
            ->first();

        if (!$imeiDetail) {
            return back()
                ->withInput()
                ->with('error', 'Chỉ được tạo phiếu bảo hành cho IMEI có trạng thái Đã bán.');
        }

        if (!$imeiDetail->order_id) {
            return back()
                ->withInput()
                ->with('error', 'IMEI này đang thiếu liên kết đơn hàng nên chưa thể tạo phiếu bảo hành.');
        }

        if ($imeiDetail->open_warranty_id) {
            return back()
                ->withInput()
                ->with('error', 'IMEI này đang có phiếu bảo hành chưa hoàn tất. Vui lòng xử lý xong phiếu hiện tại trước khi tạo phiếu mới.');
        }

        if ($this->isWarrantyPeriodExpired($imeiDetail)) {
            return back()
                ->withInput()
                ->with('error', 'IMEI này đã quá thời hạn bảo hành, không thể tạo phiếu bảo hành mới.');
        }

        $warrantyDates = $this->getWarrantyDatesFromOrder($imeiDetail);

        DB::transaction(function () use ($validated, $imeiDetail, $warrantyDates, $request) {
            $warranty = Warranty::create([
                'imei_id' => $validated['imei_id'],
                'order_id' => $imeiDetail->order_id,
                'warranty_start' => $warrantyDates['start'],
                'warranty_end' => $warrantyDates['end'],
                'status' => 'claimed',
                'customer_note' => $validated['customer_note'],
            ]);

            $this->storeWarrantyMedia(
                $warranty,
                $this->uploadedFiles($request, 'reception_media'),
                WarrantyMedia::STAGE_RECEPTION
            );

            $this->syncImeiStatus($validated['imei_id'], 'claimed');
        });

        return redirect()
            ->route('admin.warranties.index')
            ->with('success', 'Tạo phiếu bảo hành thành công.');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load([
            'imei',
            'order',
            'receptionMedia',
            'completionMedia',
            'receiptMedia',
        ]);

        $warrantyDetail = $this->warrantyDetailMap([$warranty->id])->get($warranty->id);
        $histories = $this->buildWarrantyHistory($warranty);

        return view('admin.warranties.show', compact('warranty', 'warrantyDetail', 'histories'));
    }

    public function edit(Warranty $warranty)
    {
        $warranty->load([
            'imei',
            'order',
            'receptionMedia',
            'completionMedia',
            'receiptMedia',
        ]);

        $warrantyDetail = $this->warrantyDetailMap([$warranty->id])->get($warranty->id);

        return view('admin.warranties.edit', compact('warranty', 'warrantyDetail'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::ADMIN_ALLOWED_STATUSES)],
            'status_update_note' => ['required', 'string', 'max:2000'],
            'repair_result_note' => ['nullable', 'string', 'max:3000'],

            'completion_images' => ['nullable', 'array', 'max:12'],
            'completion_images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'completion_videos' => ['nullable', 'array', 'max:5'],
            'completion_videos.*' => [
                'file',
                'mimes:mp4,mov,avi,webm,mkv',
                'max:102400',
            ],

            // Thêm validate cho minh chứng nhận máy
            'customer_receipt_note' => ['nullable', 'string', 'max:2000'],
            'receipt_images' => ['nullable', 'array', 'max:12'],
            'receipt_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'status_update_note.required' => 'Vui lòng nhập ghi chú xác nhận khi cập nhật trạng thái bảo hành.',
            'completion_images.*.image' => 'File ảnh sau sửa phải là hình ảnh hợp lệ.',
            'completion_images.*.mimes' => 'Ảnh sau sửa chỉ được là jpg, jpeg, png hoặc webp.',
            'completion_images.*.max' => 'Mỗi ảnh sau sửa không được vượt quá 10MB.',
            'completion_videos.*.mimes' => 'Video sau sửa chỉ được là mp4, mov, avi, webm hoặc mkv.',
            'completion_videos.*.max' => 'Mỗi video sau sửa không được vượt quá 100MB.',

            
        ]);

        // LOGIC KHÓA LÙI TRẠNG THÁI:
        // Chặn chuyển ngược từ "Hoàn tất xử lý" về "Đang xử lý bảo hành"
        if (in_array($warranty->status, ['active', 'expired']) && $validated['status'] === 'claimed') {
            return back()
                ->withInput()
                ->with('error', 'Phiếu bảo hành đã hoàn tất, không thể chuyển lùi lại trạng thái Đang xử lý.');
        }

        if ($validated['status'] === 'claimed') {
            if ($warranty->warranty_end && now()->startOfDay()->gt($warranty->warranty_end->copy()->startOfDay())) {
                return back()
                    ->withInput()
                    ->with('error', 'Không thể chuyển sang Đang xử lý bảo hành vì IMEI đã quá thời hạn bảo hành.');
            }
        }

        if ($validated['status'] === 'active') {
            if (blank($validated['repair_result_note'] ?? null)) {
                return back()
                    ->withInput()
                    ->with('error', 'Vui lòng nhập kết quả sửa chữa khi hoàn tất bảo hành.');
            }

            $hasExistingCompletionImage = $warranty->completionMedia()
                ->where('type', WarrantyMedia::TYPE_IMAGE)
                ->exists();

            $hasNewCompletionImage = count($this->uploadedFiles($request, 'completion_images')) > 0;

            if (!$hasExistingCompletionImage && !$hasNewCompletionImage) {
                return back()
                    ->withInput()
                    ->with('error', 'Vui lòng upload ít nhất 1 ảnh sau sửa để xác nhận đã bảo hành xong.');
            }
        }

        DB::transaction(function () use ($validated, $warranty, $request) {
            $warranty->update([
                'status' => $validated['status'],
                'status_update_note' => $validated['status_update_note'],
                'repair_result_note' => $validated['status'] === 'active'
                    ? $validated['repair_result_note']
                    : $warranty->repair_result_note,
                'customer_receipt_note' => $validated['customer_receipt_note'] ?? $warranty->customer_receipt_note, // Lưu ghi chú bàn giao
                'completed_at' => $validated['status'] === 'active'
                    ? ($warranty->completed_at ?? now())
                    : null,
            ]);

            $this->storeWarrantyMedia(
                $warranty,
                $this->uploadedFiles($request, 'completion_images'),
                WarrantyMedia::STAGE_COMPLETION
            );

            $this->storeWarrantyMedia(
                $warranty,
                $this->uploadedFiles($request, 'completion_videos'),
                WarrantyMedia::STAGE_COMPLETION
            );

            $this->storeWarrantyMedia(
                $warranty,
                $this->uploadedFiles($request, 'receipt_images'),
                WarrantyMedia::STAGE_CUSTOMER_RECEIPT
            );

            $this->syncImeiStatus($warranty->imei_id, $validated['status']);
        });

        return redirect()
            ->route('admin.warranties.show', $warranty)
            ->with('success', 'Cập nhật trạng thái bảo hành thành công.');
    }

    public function receipt(Warranty $warranty)
    {
        // Chặn nếu chưa hoàn tất xử lý
        if (!in_array($warranty->status, ['active', 'expired'])) {
            return redirect()
                ->route('admin.warranties.show', $warranty)
                ->with('error', 'Chỉ có thể cập nhật bàn giao khi phiếu bảo hành đã Hoàn tất xử lý.');
        }

        $warranty->load(['imei', 'order', 'receiptMedia']);
        return view('admin.warranties.receipt', compact('warranty'));
    }

    public function updateReceipt(Request $request, Warranty $warranty)
    {
        // Chặn nếu chưa hoàn tất xử lý
        if (!in_array($warranty->status, ['active', 'expired'])) {
            return back()->with('error', 'Chỉ có thể cập nhật bàn giao khi phiếu bảo hành đã Hoàn tất xử lý.');
        }

        $validated = $request->validate([
            'customer_receipt_note' => ['nullable', 'string', 'max:2000'],
            'receipt_images' => ['nullable', 'array', 'max:12'],
            'receipt_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        DB::transaction(function () use ($validated, $warranty, $request) {
            $warranty->update([
                'customer_receipt_note' => $validated['customer_receipt_note'],
            ]);

            // Dùng lại hàm storeWarrantyMedia đã có
            $this->storeWarrantyMedia(
                $warranty,
                $this->uploadedFiles($request, 'receipt_images'),
                WarrantyMedia::STAGE_CUSTOMER_RECEIPT
            );
        });

        return redirect()
            ->route('admin.warranties.show', $warranty)
            ->with('success', 'Cập nhật thông tin bàn giao khách hàng thành công.');
    }

    public function lookupImei(Request $request)
    {
        $imei = null;
        $imeiDetail = null;
        $currentWarranty = null;
        $currentWarrantyDetail = null;
        $warranties = collect();
        $warrantyDetails = collect();

        if ($request->filled('imei')) {
            $imei = Imei::where('imei', trim($request->imei))->first();

            if ($imei) {
                $imeiDetail = $this->imeiDetailQuery()
                    ->where('i.id', $imei->id)
                    ->first();

                $currentWarranty = Warranty::with(['order', 'receptionMedia', 'completionMedia'])
                    ->where('imei_id', $imei->id)
                    ->whereIn('status', self::OPEN_STATUSES)
                    ->latest()
                    ->first();

                if ($currentWarranty) {
                    $currentWarrantyDetail = $this->warrantyDetailMap([$currentWarranty->id])->get($currentWarranty->id);
                }

                $warranties = Warranty::with('order')
                    ->where('imei_id', $imei->id)
                    ->latest()
                    ->get();

                $warrantyDetails = $this->warrantyDetailMap($warranties->pluck('id')->all());
            }
        }

        return view('admin.warranties.lookup-imei', compact(
            'imei',
            'imeiDetail',
            'currentWarranty',
            'currentWarrantyDetail',
            'warranties',
            'warrantyDetails'
        ));
    }

    private function uploadedFiles(Request $request, string $key): array
    {
        $files = $request->file($key, []);

        if (!$files) {
            return [];
        }

        return is_array($files) ? $files : [$files];
    }

    private function storeWarrantyMedia(Warranty $warranty, array $files, string $stage): void
    {
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $mimeType = $file->getMimeType();

            $type = str_starts_with((string) $mimeType, 'video/')
                ? WarrantyMedia::TYPE_VIDEO
                : WarrantyMedia::TYPE_IMAGE;

            $path = $file->store("warranties/{$warranty->id}/{$stage}", 'public');

            WarrantyMedia::create([
                'warranty_id' => $warranty->id,
                'stage' => $stage,
                'type' => $type,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'file_size' => $file->getSize(),
            ]);
        }
    }

    private function soldImeiQuery(?string $keyword = null)
    {
        return $this->imeiDetailQuery($keyword)
            ->where('i.status', 'sold');
    }

    private function imeiDetailQuery(?string $keyword = null)
    {
        $openWarrantySub = DB::table('warranties')
            ->select('imei_id', DB::raw('MAX(id) as open_warranty_id'))
            ->whereIn('status', self::OPEN_STATUSES)
            ->groupBy('imei_id');

        return DB::table('imeis as i')
            ->join('product_variants as pv', 'pv.id', '=', 'i.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('order_items as oi', function ($join) {
                $join->on('oi.imei_id', '=', 'i.id')
                    ->orOn('oi.id', '=', 'i.reserved_by_order_item_id');
            })
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoinSub($openWarrantySub, 'ow', function ($join) {
                $join->on('ow.imei_id', '=', 'i.id');
            })
            ->when($keyword, function ($query) use ($keyword) {
                $keyword = trim($keyword);

                $query->where(function ($query) use ($keyword) {
                    $query->where('i.imei', 'like', "%{$keyword}%")
                        ->orWhere('p.name', 'like', "%{$keyword}%")
                        ->orWhere('pv.color', 'like', "%{$keyword}%")
                        ->orWhere('p.storage', 'like', "%{$keyword}%")
                        ->orWhere('o.order_code', 'like', "%{$keyword}%")
                        ->orWhere('o.customer_name', 'like', "%{$keyword}%")
                        ->orWhere('o.customer_phone', 'like', "%{$keyword}%");
                });
            })
            ->select([
                'i.id as imei_id',
                'i.imei',
                'i.status as imei_status',
                'i.reserved_by_order_item_id',

                'p.id as product_id',
                'p.name as product_name',
                'p.price as base_price',
                'p.storage',


                'pv.id as product_variant_id',
                'pv.color',
                'pv.additional_price',

                'oi.id as order_item_id',
                'oi.price as sold_price',
                'oi.quantity as sold_quantity',

                'o.id as order_id',
                'o.order_code',
                'o.customer_name',
                'o.customer_phone',
                'o.shipping_address',
                'o.status as order_status',
                'o.created_at as order_created_at',

                'ow.open_warranty_id',
            ]);
    }

    private function warrantyDetailMap(array $warrantyIds)
    {
        if (empty($warrantyIds)) {
            return collect();
        }

        return DB::table('warranties as w')
            ->join('imeis as i', 'i.id', '=', 'w.imei_id')
            ->join('product_variants as pv', 'pv.id', '=', 'i.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('order_items as oi', function ($join) {
                $join->on('oi.imei_id', '=', 'i.id')
                    ->orOn('oi.id', '=', 'i.reserved_by_order_item_id');
            })
            ->leftJoin('orders as o', 'o.id', '=', 'w.order_id')
            ->whereIn('w.id', $warrantyIds)
            ->select([
                'w.id as warranty_id',

                'i.id as imei_id',
                'i.imei',
                'i.status as imei_status',

                'p.name as product_name',
                'p.price as base_price',
                'p.storage',


                'pv.color',
                'pv.additional_price',

                'oi.price as sold_price',

                'o.order_code',
                'o.customer_name',
                'o.customer_phone',
                'o.shipping_address',
                'o.status as order_status',
                'o.created_at as order_created_at',
            ])
            ->get()
            ->keyBy('warranty_id');
    }

    private function getWarrantyDatesFromOrder($imeiDetail): array
    {
        $start = $imeiDetail->order_created_at
            ? Carbon::parse($imeiDetail->order_created_at)->startOfDay()
            : now()->startOfDay();

        $end = $start->copy()->addYear()->startOfDay();

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    private function isWarrantyPeriodExpired($imeiDetail): bool
    {
        if (!$imeiDetail || !$imeiDetail->order_created_at) {
            return false;
        }

        $warrantyEnd = Carbon::parse($imeiDetail->order_created_at)
            ->startOfDay()
            ->addYear();

        return now()->startOfDay()->gt($warrantyEnd);
    }

    private function syncImeiStatus(int $imeiId, string $warrantyStatus): void
    {
        $imeiStatus = $warrantyStatus === 'claimed' ? 'warranty' : 'sold';

        Imei::where('id', $imeiId)->update([
            'status' => $imeiStatus,
        ]);
    }

    private function buildWarrantyHistory(Warranty $warranty): array
    {
        $histories = [];

        if ($warranty->created_at) {
            $histories[] = [
                'time' => $warranty->created_at,
                'title' => 'Tạo phiếu bảo hành',
                'description' => 'Phiếu bảo hành được tạo trong hệ thống.',
            ];
        }

        if ($warranty->customer_note) {
            $histories[] = [
                'time' => $warranty->created_at ?? now(),
                'title' => 'Ghi nhận lỗi khách báo',
                'description' => $warranty->customer_note,
            ];
        }

        if ($warranty->receptionMedia && $warranty->receptionMedia->count()) {
            $histories[] = [
                'time' => $warranty->created_at ?? now(),
                'title' => 'Upload minh chứng tiếp nhận',
                'description' => 'Đã upload ' . $warranty->receptionMedia->count() . ' ảnh/video tình trạng máy lúc tiếp nhận.',
            ];
        }

        if ($warranty->status_update_note) {
            $histories[] = [
                'time' => $warranty->updated_at ?? now(),
                'title' => 'Ghi chú cập nhật trạng thái',
                'description' => $warranty->status_update_note,
            ];
        }

        if ($warranty->repair_result_note) {
            $histories[] = [
                'time' => $warranty->completed_at ?? $warranty->updated_at ?? now(),
                'title' => 'Kết quả xử lý bảo hành',
                'description' => $warranty->repair_result_note,
            ];
        }

        if ($warranty->completionMedia && $warranty->completionMedia->count()) {
            $histories[] = [
                'time' => $warranty->completed_at ?? $warranty->updated_at ?? now(),
                'title' => 'Upload minh chứng sau sửa',
                'description' => 'Đã upload ' . $warranty->completionMedia->count() . ' ảnh/video xác nhận sau khi sửa xong.',
            ];
        }

        if ($warranty->warranty_start) {
            $histories[] = [
                'time' => $warranty->warranty_start,
                'title' => 'Bắt đầu thời hạn bảo hành',
                'description' => 'Sản phẩm bắt đầu được tính thời hạn bảo hành.',
            ];
        }

        if ($warranty->warranty_end) {
            $histories[] = [
                'time' => $warranty->warranty_end,
                'title' => 'Kết thúc thời hạn bảo hành',
                'description' => 'Ngày hết hạn bảo hành thật sự của IMEI.',
            ];
        }

        if ($warranty->status === 'claimed') {
            $histories[] = [
                'time' => $warranty->updated_at,
                'title' => 'Đang xử lý bảo hành',
                'description' => 'IMEI đang được tiếp nhận và xử lý bảo hành.',
            ];
        }

        if ($warranty->customer_receipt_note) {
            $histories[] = [
                'time' => $warranty->updated_at ?? now(),
                'title' => 'Ghi chú bàn giao máy',
                'description' => $warranty->customer_receipt_note,
            ];
        }

        if ($warranty->receiptMedia && $warranty->receiptMedia->count()) {
            $histories[] = [
                'time' => $warranty->updated_at ?? now(),
                'title' => 'Upload minh chứng khách nhận',
                'description' => 'Đã upload ' . $warranty->receiptMedia->count() . ' ảnh xác nhận khách nhận lại máy.',
            ];
        }

        if (in_array($warranty->status, ['active', 'expired'], true)) {
            $histories[] = [
                'time' => $warranty->completed_at ?? $warranty->updated_at,
                'title' => 'Hoàn tất xử lý',
                'description' => 'Phiếu đã xử lý xong. IMEI có thể tạo phiếu mới nếu vẫn còn thời hạn bảo hành thật sự.',
            ];
        }

        if ($warranty->updated_at && $warranty->updated_at->ne($warranty->created_at)) {
            $histories[] = [
                'time' => $warranty->updated_at,
                'title' => 'Cập nhật gần nhất',
                'description' => 'Thông tin bảo hành được cập nhật lần gần nhất.',
            ];
        }

        return collect($histories)
            ->sortBy('time')
            ->values()
            ->all();
    }
}