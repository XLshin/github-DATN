<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    @php
    $currentUser = auth()->user();

    $isAdmin = $currentUser && $currentUser->role === 'admin';
    $isStaff = $currentUser && $currentUser->role === 'staff';

    $canViewDashboard = $isAdmin || $isStaff;
    $canViewProducts = $isAdmin || $isStaff;
    $canViewStocks = $isAdmin || $isStaff;
    $canViewOrders = $isAdmin || $isStaff;
    $canViewShipments = $isAdmin || $isStaff;
    $canViewWarranties = $isAdmin || $isStaff;
    $canViewReviews = $isAdmin || $isStaff;

    $canManageCategories = $isAdmin;
    $canManageBrands = $isAdmin;
    $canManageUsers = $isAdmin;
    $canManageCoupons = $isAdmin;
    $canManagePoints = $isAdmin;
    @endphp

    <div class="sidebar-header">
        <a class="brand-mark" href="{{ route('admin.dashboard') }}" aria-label="Byte Zone Store Admin">
            <span class="brand-icon">
                <i class="bi bi-phone" aria-hidden="true"></i>
            </span>

            <span class="brand-copy">
                <span class="brand-title">Byte Zone Store</span>
                <span class="brand-subtitle">Quản trị hệ thống</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav">
        @if ($canViewDashboard)
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            href="{{ route('admin.dashboard') }}">
            <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="nav-text">Tổng quan</span>
        </a>
        @endif

        @if ($canViewProducts)
        <a class="nav-link {{ request()->routeIs('admin.products.*', 'admin.variants.*') ? 'active' : '' }}"
            href="{{ route('admin.products.index') }}">
            <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
            <span class="nav-text">Sản phẩm</span>
        </a>
        @endif

        @if ($canManageCategories)
        <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
            href="{{ route('admin.categories.index') }}">
            <span class="nav-icon"><i class="bi bi-tags"></i></span>
            <span class="nav-text">Danh mục</span>
        </a>
        @endif

        @if ($canManageBrands)
        <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"
            href="{{ route('admin.brands.index') }}">
            <span class="nav-icon"><i class="bi bi-award"></i></span>
            <span class="nav-text">Thương hiệu</span>
        </a>
        @endif

        @if ($canViewStocks)
        <a class="nav-link {{ request()->routeIs('admin.stocks', 'admin.stocks.*', 'admin.imeis.*', 'admin.inventory.*') ? 'active' : '' }}"
            href="{{ route('admin.stocks') }}">
            <span class="nav-icon"><i class="bi bi-boxes"></i></span>
            <span class="nav-text">Kho hàng</span>
        </a>
        @endif

        @if ($canViewOrders)
        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
            href="{{ route('admin.orders.index') }}">
            <span class="nav-icon"><i class="bi bi-receipt"></i></span>
            <span class="nav-text">Đơn hàng</span>
        </a>
        @endif

        @if ($canManageCoupons && Route::has('admin.coupons.index'))
        <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"
            href="{{ route('admin.coupons.index') }}">
            <span class="nav-icon"><i class="bi bi-ticket-perforated"></i></span>
            <span class="nav-text">Voucher</span>
        </a>
        @endif

        @if ($canManagePoints && Route::has('admin.points.index'))
        <a class="nav-link {{ request()->routeIs('admin.points.*') ? 'active' : '' }}"
            href="{{ route('admin.points.index') }}">
            <span class="nav-icon"><i class="bi bi-star-fill"></i></span>
            <span class="nav-text">Quản lý điểm</span>
        </a>
        @endif

        @if ($canViewShipments && Route::has('admin.shipments.index'))
        <a class="nav-link {{ request()->routeIs('admin.shipments.*') ? 'active' : '' }}"
            href="{{ route('admin.shipments.index') }}">
            <span class="nav-icon"><i class="bi bi-truck"></i></span>
            <span class="nav-text">Vận chuyển</span>
        </a>
        @endif

        @if ($canViewWarranties)
        <a class="nav-link {{ request()->routeIs('admin.warranties.*') ? 'active' : '' }}"
            href="{{ route('admin.warranties.index') }}">
            <span class="nav-icon"><i class="bi bi-shield-check"></i></span>
            <span class="nav-text">Bảo hành</span>
        </a>
        @endif

        @if ($canViewReviews)
        <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"
            href="{{ route('admin.reviews.index') }}">
            <span class="nav-icon"><i class="bi bi-chat-dots"></i></span>
            <span class="nav-text">Đánh giá</span>
        </a>
        @endif

        @if ($isAdmin)
        <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">
            <span class="nav-icon"><i class="bi bi-image"></i></span>
            <span class="nav-text">Banner</span>
        </a>
        @endif

        @if ($canManageUsers)
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
            href="{{ route('admin.users.index') }}">
            <span class="nav-icon"><i class="bi bi-people"></i></span>
            <span class="nav-text">Người dùng</span>
        </a>
        @endif
    </nav>

    <div class="sidebar-user">
        <span class="profile-avatar sidebar-user-avatar">
            {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
        </span>

        <strong>{{ $currentUser->name ?? 'Tài khoản' }}</strong>

        <small>
            @if ($isAdmin)
            Quản trị viên
            @elseif ($isStaff)
            Nhân viên
            @else
            Người dùng
            @endif
        </small>
    </div>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">Byte Zone Store Admin</span>
    </div>
</aside>