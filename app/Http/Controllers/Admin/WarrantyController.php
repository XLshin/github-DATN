<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Imei;
use App\Models\Order;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $warranties = Warranty::with(['imei', 'order'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->keyword;

                $query->whereHas('imei', function ($q) use ($keyword) {
                    $q->where('imei', 'like', "%{$keyword}%");
                })->orWhereHas('order', function ($q) use ($keyword) {
                    $q->where('order_code', 'like', "%{$keyword}%")
                        ->orWhere('customer_name', 'like', "%{$keyword}%")
                        ->orWhere('customer_phone', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('admin.warranties.index', compact('warranties'));
    }

    public function create()
    {
        return view('admin.warranties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_code' => ['required', 'exists:orders,order_code'],
            'imei' => ['required', 'exists:imeis,imei'],
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date', 'after_or_equal:warranty_start'],
            'status' => ['required', Rule::in(['active', 'expired', 'claimed'])],
        ]);

        $order = Order::where('order_code', $validated['order_code'])->firstOrFail();
        $imei = Imei::where('imei', $validated['imei'])->firstOrFail();

        $exists = Warranty::where('imei_id', $imei->id)
            ->whereIn('status', ['active', 'claimed'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'IMEI này đã có phiếu bảo hành đang hiệu lực.');
        }

        DB::transaction(function () use ($validated, $order, $imei) {
            Warranty::create([
                'imei_id' => $imei->id,
                'order_id' => $order->id,
                'warranty_start' => $validated['warranty_start'],
                'warranty_end' => $validated['warranty_end'],
                'status' => $validated['status'],
            ]);

            if ($validated['status'] === 'claimed') {
                $imei->update([
                    'status' => 'warranty',
                ]);
            }
        });

        return redirect()
            ->route('admin.warranties.index')
            ->with('success', 'Tạo phiếu bảo hành thành công.');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load(['imei', 'order']);

        $histories = $this->buildWarrantyHistory($warranty);

        return view('admin.warranties.show', compact('warranty', 'histories'));
    }

    public function edit(Warranty $warranty)
    {
        return view('admin.warranties.edit', compact('warranty'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $validated = $request->validate([
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date', 'after_or_equal:warranty_start'],
            'status' => ['required', Rule::in(['active', 'expired', 'claimed'])],
        ]);

        DB::transaction(function () use ($validated, $warranty) {
            $warranty->update($validated);

            if ($validated['status'] === 'claimed') {
                $warranty->imei->update([
                    'status' => 'warranty',
                ]);
            }

            if ($validated['status'] === 'expired') {
                $warranty->imei->update([
                    'status' => 'sold',
                ]);
            }
        });

        return redirect()
            ->route('admin.warranties.show', $warranty)
            ->with('success', 'Cập nhật bảo hành thành công.');
    }

    public function updateStatus(Request $request, Warranty $warranty)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'expired', 'claimed'])],
        ]);

        DB::transaction(function () use ($validated, $warranty) {
            $warranty->update([
                'status' => $validated['status'],
            ]);

            if ($validated['status'] === 'claimed') {
                $warranty->imei->update([
                    'status' => 'warranty',
                ]);
            }

            if ($validated['status'] === 'expired') {
                $warranty->imei->update([
                    'status' => 'sold',
                ]);
            }
        });

        return back()->with('success', 'Cập nhật trạng thái bảo hành thành công.');
    }

    public function lookupImei(Request $request)
{
    $imei = null;
    $currentWarranty = null;
    $warranties = collect();

    if ($request->filled('imei')) {
        $imei = Imei::where('imei', $request->imei)->first();

        if ($imei) {
            $currentWarranty = Warranty::with('order')
                ->where('imei_id', $imei->id)
                ->whereIn('status', ['active', 'claimed'])
                ->latest()
                ->first();

            $warranties = Warranty::with('order')
                ->where('imei_id', $imei->id)
                ->latest()
                ->get();
        }
    }

    return view('admin.warranties.lookup-imei', compact(
        'imei',
        'currentWarranty',
        'warranties'
    ));
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

        $histories[] = [
            'time' => $warranty->warranty_start,
            'title' => 'Bắt đầu bảo hành',
            'description' => 'Sản phẩm bắt đầu thời gian bảo hành.',
        ];

        $histories[] = [
            'time' => $warranty->warranty_end,
            'title' => 'Kết thúc bảo hành',
            'description' => 'Ngày hết hạn bảo hành theo phiếu.',
        ];

        if ($warranty->status === 'claimed') {
            $histories[] = [
                'time' => $warranty->updated_at,
                'title' => 'Yêu cầu bảo hành',
                'description' => 'IMEI đang được xử lý bảo hành.',
            ];
        }

        if ($warranty->status === 'expired') {
            $histories[] = [
                'time' => $warranty->updated_at,
                'title' => 'Hết hạn bảo hành',
                'description' => 'Phiếu bảo hành đã chuyển sang trạng thái hết hạn.',
            ];
        }

        if ($warranty->updated_at && $warranty->updated_at->ne($warranty->created_at)) {
            $histories[] = [
                'time' => $warranty->updated_at,
                'title' => 'Cập nhật gần nhất',
                'description' => 'Thông tin bảo hành được cập nhật lần gần nhất.',
            ];
        }

        return $histories;
    }
}