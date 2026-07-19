<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Warranty;
use Illuminate\Http\Request;

class ClientWarrantyController extends Controller
{
    /**
     * Hiển thị danh sách tất cả phiếu bảo hành
     * thuộc các đơn hàng của người dùng hiện tại.
     */
    public function showLookupForm(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));

        $orderItems = OrderItem::query()
            ->with([
                'product',
                'variant',
                'order',

                // Một IMEI có thể có nhiều phiếu bảo hành.
                'imeis.warranties' => function ($query) {
                    $query->latest('created_at');
                },
            ])
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->getKey());
            })
            ->whereHas('imeis.warranties')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->get();

        return view('client.warranties.lookup', compact(
            'search',
            'orderItems'
        ));
    }

    /**
     * Hiển thị chi tiết một phiếu bảo hành.
     */
    public function show(Request $request, Warranty $warranty)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        /*
         * Load toàn bộ dữ liệu cần dùng trong trang chi tiết.
         */
        $warranty->load([
            'order',
            'imei.productVariant.product',
            'replacementImei.productVariant.product',
            'receptionMedia',
            'completionMedia',
            'receiptMedia',
        ]);

        /*
         * Chỉ cho phép khách hàng xem phiếu bảo hành
         * thuộc đơn hàng của chính tài khoản đó.
         */
        if (
            ! $warranty->order
            || (int) $warranty->order->user_id !== (int) $user->getKey()
        ) {
            abort(403);
        }

        $histories = $this->buildWarrantyHistory($warranty);

        return view('client.warranties.show', compact(
            'warranty',
            'histories'
        ));
    }

    /**
     * Tạo danh sách lịch sử sự kiện của phiếu bảo hành.
     */
    private function buildWarrantyHistory(Warranty $warranty): array
    {
        $histories = [];

        /*
         * Sự kiện tạo phiếu.
         */
        if ($warranty->created_at) {
            $histories[] = [
                'time' => $warranty->created_at,
                'title' => 'Tạo phiếu bảo hành',
                'description' => 'Phiếu bảo hành được tạo trong hệ thống.',
            ];
        }

        /*
         * Ghi nhận lỗi do khách hàng cung cấp.
         */
        if (filled($warranty->customer_note)) {
            $histories[] = [
                'time' => $warranty->created_at ?? now(),
                'title' => 'Ghi nhận lỗi khách báo',
                'description' => $warranty->customer_note,
            ];
        }

        /*
         * Ghi nhận minh chứng lúc tiếp nhận máy.
         */
        if ($warranty->receptionMedia->isNotEmpty()) {
            $histories[] = [
                'time' => $warranty->receptionMedia
                    ->sortBy('created_at')
                    ->first()?->created_at ?? $warranty->created_at,
                'title' => 'Tiếp nhận thiết bị',
                'description' => 'Đã cập nhật ảnh hoặc video minh chứng lúc tiếp nhận thiết bị.',
            ];
        }

        /*
         * Bắt đầu thời hạn bảo hành.
         */
        if ($warranty->warranty_start) {
            $histories[] = [
                'time' => $warranty->warranty_start,
                'title' => 'Bắt đầu thời hạn bảo hành',
                'description' => 'Sản phẩm bắt đầu được tính thời hạn bảo hành.',
            ];
        }

        /*
         * Phiếu đang được tiếp nhận hoặc xử lý.
         */
        if ($warranty->status === Warranty::STATUS_CLAIMED) {
            $histories[] = [
                'time' => $warranty->updated_at ?? $warranty->created_at,
                'title' => 'Đang xử lý bảo hành',
                'description' => filled($warranty->status_update_note)
                    ? $warranty->status_update_note
                    : 'IMEI đang được tiếp nhận và xử lý bảo hành.',
            ];
        }

        if ($warranty->resolution_type === Warranty::RESOLUTION_REPLACE && $warranty->replaced_at) {
            $histories[] = [
                'time' => $warranty->replaced_at,
                'title' => 'Đổi máy mới',
                'description' => 'Sản phẩm đã được đổi máy mới theo chính sách 30 ngày. IMEI mới: ' . ($warranty->replacementImei?->imei ?? 'Đang cập nhật'),
            ];
        }

        /*
         * Có kết quả sửa chữa.
         */
        if (
            filled($warranty->repair_result_note)
            || $warranty->completionMedia->isNotEmpty()
        ) {
            $completionTime = $warranty->completionMedia
                ->sortByDesc('created_at')
                ->first()?->created_at
                ?? $warranty->updated_at
                ?? $warranty->created_at;

            $histories[] = [
                'time' => $completionTime,
                'title' => 'Cập nhật kết quả kỹ thuật',
                'description' => filled($warranty->repair_result_note)
                    ? $warranty->repair_result_note
                    : 'Đã cập nhật ảnh hoặc video kết quả sửa chữa.',
            ];
        }

        /*
         * Phiếu đã hoàn tất xử lý.
         */
        if (
            in_array(
                $warranty->status,
                [
                    Warranty::STATUS_ACTIVE,
                    Warranty::STATUS_EXPIRED,
                ],
                true
            )
        ) {
            $histories[] = [
                'time' => $warranty->updated_at ?? $warranty->created_at,
                'title' => 'Hoàn tất xử lý',
                'description' => 'Phiếu bảo hành đã được xử lý xong. Khách hàng có thể đến nhận lại thiết bị.',
            ];
        }

        /*
         * Đã có minh chứng bàn giao cho khách hàng.
         */
        if ($warranty->receiptMedia->isNotEmpty()) {
            $receiptTime = $warranty->receiptMedia
                ->sortByDesc('created_at')
                ->first()?->created_at
                ?? $warranty->updated_at
                ?? $warranty->created_at;

            $histories[] = [
                'time' => $receiptTime,
                'title' => 'Đã bàn giao cho khách hàng',
                'description' => filled($warranty->customer_receipt_note)
                    ? $warranty->customer_receipt_note
                    : 'Thiết bị đã được bàn giao và có minh chứng xác nhận.',
            ];
        } elseif (filled($warranty->customer_receipt_note)) {
            $histories[] = [
                'time' => $warranty->updated_at ?? $warranty->created_at,
                'title' => 'Cập nhật thông tin bàn giao',
                'description' => $warranty->customer_receipt_note,
            ];
        }

        /*
         * Ngày hết hạn bảo hành thật sự của IMEI.
         */
        if ($warranty->warranty_end) {
            $histories[] = [
                'time' => $warranty->warranty_end,
                'title' => 'Kết thúc thời hạn bảo hành',
                'description' => 'Ngày hết hạn bảo hành thật sự của IMEI.',
            ];
        }

        return collect($histories)
            ->filter(function ($history) {
                return ! empty($history['time']);
            })
            ->sortBy('time')
            ->values()
            ->all();
    }
}