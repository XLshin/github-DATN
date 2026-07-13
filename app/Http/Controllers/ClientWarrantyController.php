<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Warranty;
use Illuminate\Http\Request;

class ClientWarrantyController extends Controller
{
    public function showLookupForm(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));

        $orderItems = OrderItem::query()
            ->with(['product', 'variant', 'order', 'imeis.warranty'])
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id))
            ->whereHas('imeis.warranty')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
            })
            ->latest('id')
            ->get();

        return view('client.warranties.lookup', compact('search', 'orderItems'));
    }

    public function show(Warranty $warranty)
    {
        $user = auth()->user();

        // Only allow viewing if warranty is linked to an order owned by the user
        if (! $warranty->order || (int) $warranty->order->user_id !== (int) $user->getKey()) {
            abort(403);
        }

        $warranty->load(['imei', 'order', 'receptionMedia', 'completionMedia', 'receiptMedia']);

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
