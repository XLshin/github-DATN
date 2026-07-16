<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PendingAdminRequestsService;

class NotificationController extends Controller
{
    public function __construct(
        private readonly PendingAdminRequestsService $pendingService,
    ) {}

    /**
     * Trả về số lượng + danh sách mới nhất các yêu cầu đang chờ admin xử lý, dùng cho JS
     * polling để hiện thông báo "nhảy ra" khi có yêu cầu mới, không cần tải lại trang.
     */
    public function pendingCount()
    {
        $sorted = $this->pendingService->get()->map(function (array $item) {
            $item['time'] = $item['time']?->toIso8601String();

            return $item;
        });

        return response()->json([
            'count' => $sorted->count(),
            'items' => $sorted->take(10)->values()->all(),
        ]);
    }
}
