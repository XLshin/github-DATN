<nav class="navbar admin-navbar navbar-expand bg-white">
    <div class="container-fluid px-3 px-lg-4">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span><span></span><span></span>
        </button>

        <div class="navbar-actions ms-auto">
            <div class="dropdown admin-notification-dropdown">
                <button class="icon-button position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo yêu cầu chờ xử lý" data-admin-notif-bell>
                    <i class="bi bi-bell" data-admin-notif-bell-icon aria-hidden="true"></i>
                    <span data-admin-notif-badge
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ ($adminPendingNotificationsCount ?? 0) > 0 ? '' : 'd-none' }}"
                          style="font-size:.65rem">
                        {{ ($adminPendingNotificationsCount ?? 0) > 99 ? '99+' : ($adminPendingNotificationsCount ?? 0) }}
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" style="width:360px;max-height:420px;overflow-y:auto">
                    <div class="px-3 py-2 border-bottom fw-bold d-flex justify-content-between align-items-center">
                        <span>Yêu cầu chờ xử lý</span>
                    </div>

                    <div data-admin-notif-menu>
                        @forelse(($adminPendingNotifications ?? []) as $notification)
                            <a href="{{ $notification['url'] }}" class="dropdown-item d-flex align-items-start gap-2 py-2 px-3 border-bottom">
                                <i class="bi {{ $notification['icon'] }} text-primary mt-1"></i>
                                <span class="flex-grow-1">
                                    <span class="d-block fw-semibold small">{{ $notification['type'] }} — {{ $notification['user'] }}</span>
                                    <span class="d-block text-muted small">
                                        {{ number_format($notification['amount'], 0, ',', '.') }} đ
                                        · {{ $notification['time']?->diffForHumans() }}
                                    </span>
                                </span>
                            </a>
                        @empty
                            <div class="px-3 py-4 text-center text-muted small">Không có yêu cầu nào đang chờ xử lý.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme">
                <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
                <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="profile-avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="profile-name d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">Tài khoản</a></li>
                    <li><a class="dropdown-item" href="{{ url('/') }}">Cửa hàng</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Đăng xuất</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

{{-- Modal tổng hợp công việc cần xử lý — chỉ hiện 1 lần khi bắt đầu phiên làm việc (đăng nhập/mở lại trình duyệt) --}}
<div class="modal fade" id="adminPendingTasksModal" tabindex="-1" aria-hidden="true"
     data-admin-pending-count="{{ $adminPendingNotificationsCount ?? 0 }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-circle text-warning me-1"></i>
                    Có {{ $adminPendingNotificationsCount ?? 0 }} việc cần xử lý
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="max-height:420px;overflow-y:auto">
                @forelse(($adminPendingNotifications ?? []) as $notification)
                    <a href="{{ $notification['url'] }}" class="d-flex align-items-start gap-2 py-2 px-3 border-bottom text-decoration-none">
                        <i class="bi {{ $notification['icon'] }} text-primary mt-1"></i>
                        <span class="flex-grow-1">
                            <span class="d-block fw-semibold small text-dark">{{ $notification['type'] }} — {{ $notification['user'] }}</span>
                            <span class="d-block text-muted small">
                                {{ number_format($notification['amount'], 0, ',', '.') }} đ
                                · {{ $notification['time']?->diffForHumans() }}
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="px-3 py-4 text-center text-muted small">Không có yêu cầu nào đang chờ xử lý.</div>
                @endforelse
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Để sau</button>
                <a href="{{ route('admin.bank-transaction-logs.index') }}" class="btn btn-primary btn-sm">Xem tất cả</a>
            </div>
        </div>
    </div>
</div>
