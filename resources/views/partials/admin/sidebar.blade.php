<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="{{ route('admin.dashboard') }}" aria-label="H-Phone Admin">
            <span class="brand-icon"><i class="bi bi-phone" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">Byte Zone Store</span>
                <span class="brand-subtitle">Quản trị hệ thống</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="nav-text">Tổng quan</span>
        </a>

        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
            <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
            <span class="nav-text">Sản phẩm</span>
        </a>
        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
            <span class="nav-icon"><i class="bi bi-tags"></i></span>
            <span class="nav-text">Danh mục</span>
        </a>
        <a class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}" href="{{ route('brands.index') }}">
            <span class="nav-icon"><i class="bi bi-award"></i></span>
            <span class="nav-text">Thương hiệu</span>
        </a>

        <a class="nav-link {{ request()->routeIs('admin.inventory.*', 'admin.stocks') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}">
            <span class="nav-icon"><i class="bi bi-boxes"></i></span>
            <span class="nav-text">Kho hàng</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.imeis.*') ? 'active' : '' }}" href="{{ route('admin.imeis.index') }}">
            <span class="nav-icon"><i class="bi bi-upc-scan"></i></span>
            <span class="nav-text">IMEI</span>
        </a>

        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
            <span class="nav-icon"><i class="bi bi-receipt"></i></span>
            <span class="nav-text">Đơn hàng</span>
        </a>
        <a class="nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}" href="{{ route('coupons.index') }}">
            <span class="nav-icon"><i class="bi bi-ticket-perforated"></i></span>
            <span class="nav-text">Voucher</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.points.*') ? 'active' : '' }}" href="{{ route('admin.points.index') }}">
            <span class="nav-icon"><i class="bi bi-star-fill"></i></span>
            <span class="nav-text">Quản lý điểm</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.shipments.*') ? 'active' : '' }}" href="{{ route('admin.shipments.index') }}">
            <span class="nav-icon"><i class="bi bi-truck"></i></span>
            <span class="nav-text">Vận chuyển</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.warranties.*') ? 'active' : '' }}" href="{{ route('admin.warranties.index') }}">
            <span class="nav-icon"><i class="bi bi-shield-check"></i></span>
            <span class="nav-text">Bảo hành</span>
        </a>

        <a class="nav-link {{ request()->routeIs('reviews.index', 'reviews.hide', 'reviews.destroy') ? 'active' : '' }}" href="{{ route('reviews.index') }}">
            <span class="nav-icon"><i class="bi bi-chat-dots"></i></span>
            <span class="nav-text">Đánh giá</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <span class="nav-icon"><i class="bi bi-people"></i></span>
            <span class="nav-text">Người dùng</span>
        </a>
    </nav>

    <div class="sidebar-user">
        <span class="profile-avatar sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        <strong>{{ auth()->user()->name }}</strong>
        <small>{{ auth()->user()->isAdmin() ? 'Quản trị viên' : 'Nhân viên' }}</small>
    </div>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">H-Phone Store Admin</span>
    </div>
</aside>
