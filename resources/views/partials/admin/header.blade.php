<nav class="navbar admin-navbar navbar-expand bg-white">
    <div class="container-fluid px-3 px-lg-4">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span><span></span><span></span>
        </button>

        <div class="navbar-actions ms-auto">
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
