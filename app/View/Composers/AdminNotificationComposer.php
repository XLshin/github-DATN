<?php

namespace App\View\Composers;

use App\Services\PendingAdminRequestsService;
use Illuminate\View\View;

/**
 * Gom các yêu cầu từ khách hàng đang chờ admin xử lý để hiển thị chuông thông báo trên giao diện quản trị.
 */
class AdminNotificationComposer
{
    public function __construct(
        private readonly PendingAdminRequestsService $pendingService,
    ) {}

    public function compose(View $view): void
    {
        $sorted = $this->pendingService->get();

        $view->with('adminPendingNotifications', $sorted->take(10));
        $view->with('adminPendingNotificationsCount', $sorted->count());
    }
}
