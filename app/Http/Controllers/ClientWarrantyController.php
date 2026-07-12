<?php

namespace App\Http\Controllers;

use App\Models\Imei;
use App\Models\Warranty;
use Illuminate\Http\Request;

class ClientWarrantyController extends Controller
{
    public function showLookupForm()
    {
        $user = auth()->user();
        $userImeis = [];

        if ($user) {
            $userImeis = Imei::whereHas('orderItem.order', function ($q) use ($user) {
                $q->where('user_id', $user->getKey());
            })->pluck('imei')->unique()->values()->all();
        }

        return view('client.warranties.lookup', compact('userImeis'));
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'imei' => ['nullable', 'string'],
            'order_code' => ['nullable', 'string'],
        ]);

        $imei = null;
        $currentWarranty = null;
        $warranties = collect();
        $user = auth()->user();

        if ($request->filled('imei')) {
            $imei = Imei::where('imei', trim($request->imei))->first();

            if ($imei) {
                $query = Warranty::with('order')
                    ->where('imei_id', $imei->id)
                    ->whereIn('status', ['claimed']);

                if ($user) {
                    $query->whereHas('order', function ($q) use ($user) {
                        $q->where('user_id', $user->getKey());
                    });
                }

                $currentWarranty = (clone $query)->latest()->first();

                $warranties = (clone $query)->latest()->get();
            }
        }

        if ($request->filled('order_code') && (is_array($warranties) ? empty($warranties) : $warranties->isEmpty())) {
            $orderQuery = Warranty::with('order')
                ->whereHas('order', function ($q) use ($request, $user) {
                    $q->where('order_code', trim($request->order_code));
                    if ($user) {
                        $q->where('user_id', $user->getKey());
                    }
                });

            $warranties = $orderQuery->latest()->get();
        }

        $user = auth()->user();
        $userImeis = [];

        if ($user) {
            $userImeis = Imei::whereHas('orderItem.order', function ($q) use ($user) {
                $q->where('user_id', $user->getKey());
            })->pluck('imei')->unique()->values()->all();
        }

        return view('client.warranties.lookup', compact('imei', 'currentWarranty', 'warranties', 'userImeis'));
    }

    public function show(Warranty $warranty)
    {
        $user = auth()->user();

        // Only allow viewing if warranty is linked to an order owned by the user
        if (! $warranty->order || (int) $warranty->order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        $warranty->load(['imei', 'order']);

        $histories = $this->buildWarrantyHistory($warranty);

        return view('client.warranties.show', compact('warranty', 'histories'));
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

        if ($warranty->status === Warranty::STATUS_CLAIMED) {
            $histories[] = [
                'time' => $warranty->updated_at,
                'title' => 'Đang xử lý bảo hành',
                'description' => 'IMEI đang được tiếp nhận và xử lý bảo hành.',
            ];
        }

        if (in_array($warranty->status, [Warranty::STATUS_ACTIVE, Warranty::STATUS_EXPIRED], true)) {
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
