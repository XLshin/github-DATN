<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Imei;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarrantyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Trạng thái xử lý bảo hành
    |--------------------------------------------------------------------------
    | claimed = Đang xử lý bảo hành
    | active  = Hoàn tất xử lý
    |
    | expired vẫn giữ trong DB cũ để không lỗi dữ liệu cũ,
    | nhưng từ giao diện mới admin không chọn expired nữa.
    */
    private const OPEN_STATUSES = ['claimed'];

    private const ADMIN_ALLOWED_STATUSES = ['claimed', 'active'];

    public function index(Request $request)
    {
        $warranties = Warranty::with(['imei', 'order'])
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
            'customer_note' => ['nullable', 'string', 'max:2000'],
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

        DB::transaction(function () use ($validated, $imeiDetail, $warrantyDates) {
            Warranty::create([
                'imei_id' => $validated['imei_id'],
                'order_id' => $imeiDetail->order_id,
                'warranty_start' => $warrantyDates['start'],
                'warranty_end' => $warrantyDates['end'],
                'status' => 'claimed',
                'customer_note' => $validated['customer_note'] ?? null,
            ]);

            $this->syncImeiStatus($validated['imei_id'], 'claimed');
        });

        return redirect()
            ->route('admin.warranties.index')
            ->with('success', 'Tạo phiếu bảo hành thành công.');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load(['imei', 'order']);

        $warrantyDetail = $this->warrantyDetailMap([$warranty->id])->get($warranty->id);
        $histories = $this->buildWarrantyHistory($warranty);

        return view('admin.warranties.show', compact('warranty', 'warrantyDetail', 'histories'));
    }

    public function edit(Warranty $warranty)
    {
        $warranty->load(['imei', 'order']);

        $warrantyDetail = $this->warrantyDetailMap([$warranty->id])->get($warranty->id);

        return view('admin.warranties.edit', compact('warranty', 'warrantyDetail'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $validated = $request->validate([
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date', 'after_or_equal:warranty_start'],
            'status' => ['required', Rule::in(self::ADMIN_ALLOWED_STATUSES)],
            'customer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'claimed') {
            if (now()->startOfDay()->gt(Carbon::parse($validated['warranty_end'])->startOfDay())) {
                return back()
                    ->withInput()
                    ->with('error', 'Không thể chuyển sang Đang xử lý bảo hành vì IMEI đã quá thời hạn bảo hành.');
            }
        }

        DB::transaction(function () use ($validated, $warranty) {
            $warranty->update($validated);

            $this->syncImeiStatus($warranty->imei_id, $validated['status']);
        });

        return redirect()
            ->route('admin.warranties.show', $warranty)
            ->with('success', 'Cập nhật bảo hành thành công.');
    }

    public function updateStatus(Request $request, Warranty $warranty)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::ADMIN_ALLOWED_STATUSES)],
        ]);

        if ($validated['status'] === 'claimed') {
            if ($warranty->warranty_end && now()->startOfDay()->gt($warranty->warranty_end->copy()->startOfDay())) {
                return back()
                    ->with('error', 'Không thể chuyển sang Đang xử lý bảo hành vì IMEI đã quá thời hạn bảo hành.');
            }
        }

        DB::transaction(function () use ($validated, $warranty) {
            $warranty->update([
                'status' => $validated['status'],
            ]);

            $this->syncImeiStatus($warranty->imei_id, $validated['status']);
        });

        return back()->with('success', 'Cập nhật trạng thái bảo hành thành công.');
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

                $currentWarranty = Warranty::with('order')
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
                        ->orWhere('pv.storage', 'like', "%{$keyword}%")
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

                'pv.id as product_variant_id',
                'pv.color',
                'pv.storage',
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

                'pv.color',
                'pv.storage',
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

        if (in_array($warranty->status, ['active', 'expired'], true)) {
            $histories[] = [
                'time' => $warranty->updated_at,
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