{{-- ===== TOP BAR (cuộn theo trang) ===== --}}
<div class="header-topbar" style="padding: 0; line-height: 0;">
    <a href="{{ route('home') }}" style="display:block;">
        <img src="{{ asset('assets-client/images/banner/banner top.png') }}" alt="Banner khuyến mãi" style="width: 100%; height: auto; display: block;">
    </a>
</div>

<header class="site-header">
    {{-- ===== HEADER MIDDLE: Logo + Search + Login + Cart (sticky) ===== --}}
    <div class="header-middle">
        <div class="container">
            <div class="d-flex align-items-center gap-4">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="header-logo flex-shrink-0">
                    <img src="{{ asset('assets-client/images/logo/logo.png') }}" alt="ByteZone" height="100" width=>
                </a>

                {{-- Search --}}
                <div class="header-search-wrap flex-grow-1" style="position:relative; max-width:620px;">
                    <form action="{{ route('home') }}" method="GET" id="headerSearchForm">
                        <div class="search-wrap">
                            <input type="text" name="search" id="headerSearchInput"
                                value="{{ request('search') }}"
                                placeholder="Tìm theo tên, màu, dung lượng, giá..." autocomplete="off">
                            {{-- hidden filters --}}
                            <input type="hidden" name="color" id="hsColor" value="{{ request('color') }}">
                            <input type="hidden" name="storage" id="hsStorage" value="{{ request('storage') }}">
                            <input type="hidden" name="price_min" id="hsPriceMin" value="{{ request('price_min') }}">
                            <input type="hidden" name="price_max" id="hsPriceMax" value="{{ request('price_max') }}">
                            <button type="button" id="searchFilterToggle" title="Lọc nâng cao"
                                style="background:none;border:none;color:#1565c0;padding:0 10px;font-size:16px;cursor:pointer;">
                                <i class="lni lni-funnel"></i>
                            </button>
                            <button type="submit"><i class="lni lni-search-alt"></i></button>
                        </div>

                        {{-- Filter dropdown --}}
                        <div id="searchFilterPanel" style="display:none; position:absolute; top:calc(100% + 6px); left:0; right:0;
                            background:#fff; border:1.5px solid #1565c0; border-radius:12px; padding:16px;
                            box-shadow:0 8px 24px rgba(0,0,0,.12); z-index:1050;">

                            <div class="row g-2">
                                <div class="col-6">
                                    <label style="font-size:12px; font-weight:600; color:#555; margin-bottom:4px; display:block;">Màu sắc</label>
                                    <select id="fsColor" class="form-select form-select-sm">
                                        <option value="">Tất cả màu</option>
                                        @foreach(\App\Models\ProductVariant::distinct()->pluck('color')->filter()->sort() as $c)
                                        <option value="{{ $c }}" @selected(request('color')===$c)>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label style="font-size:12px; font-weight:600; color:#555; margin-bottom:4px; display:block;">Dung lượng</label>
                                    <select id="fsStorage" class="form-select form-select-sm">
                                        <option value="">Tất cả</option>
                                        @foreach(\App\Models\Product::distinct()->pluck('storage')->filter()->sort() as $s)
                                        <option value="{{ $s }}" @selected(request('storage')===$s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label style="font-size:12px; font-weight:600; color:#555; margin-bottom:4px; display:block;">Giá từ (₫)</label>
                                    <input type="number" id="fsPriceMin" class="form-control form-control-sm"
                                        placeholder="0" value="{{ request('price_min') }}">
                                </div>
                                <div class="col-6">
                                    <label style="font-size:12px; font-weight:600; color:#555; margin-bottom:4px; display:block;">Giá đến (₫)</label>
                                    <input type="number" id="fsPriceMax" class="form-control form-control-sm"
                                        placeholder="Không giới hạn" value="{{ request('price_max') }}">
                                </div>
                                <div class="col-12 d-flex gap-2 justify-content-end mt-1">
                                    <button type="button" id="fsClear"
                                        class="btn btn-sm btn-outline-secondary">Xóa lọc</button>
                                    <button type="submit" id="fsApply"
                                        class="btn btn-sm btn-primary">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Login / User --}}
                <div class="header-account flex-shrink-0">
                    @auth
                    <div class="account-dropdown">
                        <button class="account-btn">
                            <i class="lni lni-user"></i>
                            <span class="d-none d-lg-inline ms-1">{{ Str::limit(auth()->user()->name, 12) }}</span>
                            <i class="lni lni-chevron-down ms-1" style="font-size:10px"></i>
                        </button>
                        <ul class="account-menu">
                            @if(auth()->user()->role === 'customer')
                            <li><a href="{{ route('dashboard') }}"><i class="lni lni-user me-2"></i>Tài khoản</a></li>
                            <li><a href="{{ route('orders.index') }}"><i class="lni lni-package me-2"></i>Đơn hàng</a></li>
                            @else
                            <li><a href="{{ route('admin.dashboard') }}"><i class="lni lni-dashboard me-2"></i>Quản trị</a></li>
                            @endif
                            <li class="divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"><i class="lni lni-exit me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="header-login-btn">
                        <i class="lni lni-user"></i>
                        <span class="d-none d-lg-inline ms-1">Đăng nhập</span>
                    </a>
                    @endauth
                </div>

                {{-- Cart --}}
                <div class="header-cart flex-shrink-0">
                    @auth
                    @if(auth()->user()->role === 'customer')
                    <a href="{{ route('cart.index') }}" class="cart-btn">
                        <i class="lni lni-cart"></i>
                        <span class="cart-text">Giỏ hàng</span>
                        <span class="cart-badge" id="cart-count">0</span>
                    </a>
                    @else
                    <a href="{{ route('admin.dashboard') }}" class="cart-btn">
                        <i class="lni lni-cart"></i>
                        <span class="cart-text">Giỏ hàng</span>
                    </a>
                    @endif
                    @else
                    <a href="{{ route('login') }}" class="cart-btn">
                        <i class="lni lni-cart"></i>
                        <span class="cart-text">Giỏ hàng</span>
                    </a>
                    @endauth
                </div>


                        @guest
                        <div class="navbar-cart">
                            <div class="cart-items">
                                <a href="{{ route('login') }}" class="main-btn">
                                    <i class="lni lni-cart"></i>
                                    <span class="total-items">0</span>
                                </a>

                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>Giỏ hàng</span>
                                        <a href="{{ route('login') }}">Đăng nhập</a>
                                    </div>

                                    <ul class="shopping-list">
                                        <li>
                                            <div class="content">
                                                <h4>
                                                    <a href="{{ route('login') }}">
                                                        Đăng nhập để xem giỏ hàng
                                                    </a>
                                                </h4>
                                                <p class="quantity">
                                                    Bạn cần đăng nhập để mua hàng và thanh toán.
                                                </p>
                                            </div>
                                        </li>
                                    </ul>

                                    <div class="bottom">
                                        <div class="button">
                                            <a href="{{ route('login') }}" class="btn animate">
                                                Đăng nhập
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endguest

                        @auth
                        @if (auth()->user()->role === 'customer')
                        <div class="navbar-cart">
                            <div class="wishlist">
                                <a href="javascript:void(0)">
                                    <i class="lni lni-heart"></i>
                                    <span class="total-items">0</span>
                                </a>
                            </div>

                            <div class="cart-items">
                                <a href="{{ route('cart.index') }}" class="main-btn">
                                    <i class="lni lni-cart" id="cart-fly-target"></i>
                                    <span class="total-items" id="nav-cart-count">{{ app(\App\Services\CartService::class)->getCartCount(auth()->user()) }}</span>
                                </a>

                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>Giỏ hàng</span>
                                        <a href="{{ route('cart.index') }}">Xem giỏ hàng</a>
                                    </div>

                                    <ul class="shopping-list">
                                        <li>
                                            <div class="content">
                                                <h4>
                                                    <a href="{{ route('cart.index') }}">
                                                        Xem sản phẩm trong giỏ hàng
                                                    </a>
                                                </h4>
                                                <p class="quantity">
                                                    Quản lý sản phẩm, số lượng và thanh toán.
                                                </p>
                                            </div>
                                        </li>
                                    </ul>

                                    <div class="bottom">
                                        <div class="button">
                                            <a href="{{ route('cart.index') }}" class="btn animate">
                                                Đi đến giỏ hàng
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif (in_array(auth()->user()->role, ['admin', 'staff'], true))
                        <div class="navbar-cart">
                            <div class="cart-items">
                                <a href="{{ route('admin.dashboard') }}" class="main-btn">
                                    <i class="lni lni-dashboard"></i>
                                </a>

                                <div class="shopping-item">
                                    <div class="dropdown-cart-header">
                                        <span>Trang quản trị</span>
                                        <a href="{{ route('admin.dashboard') }}">Vào quản trị</a>
                                    </div>

                                    <ul class="shopping-list">
                                        <li>
                                            <div class="content">
                                                <h4>
                                                    <a href="{{ route('admin.dashboard') }}">
                                                        Truy cập hệ thống quản trị
                                                    </a>
                                                </h4>
                                                <p class="quantity">
                                                    Quản lý đơn hàng, kho hàng, bảo hành và các nghiệp vụ nội bộ.
                                                </p>
                                            </div>
                                        </li>
                                    </ul>

                                    <div class="bottom">
                                        <div class="button">
                                            <a href="{{ route('admin.dashboard') }}" class="btn animate">
                                                Vào trang quản trị
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

</header>

{{-- ===== HEADER MAIN: Nav danh mục (cuộn theo trang) ===== --}}
<div class="header-main">
    <div class="container">
        <nav class="main-nav d-flex align-items-center justify-content-center">

            {{-- Mobile toggle --}}
            <button class="mobile-nav-toggle d-lg-none me-3" id="mobileNavToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>

            <ul class="main-nav-list" id="mainNavList">
                {{-- Danh mục động từ DB --}}
                @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                @php
                // Danh mục Điện thoại hiện có ID = 1; dùng ID để tránh lỗi so sánh chuỗi có dấu/encoding.
                $categoryUrl = route('products.index', ['category_id' => $cat->id]);
                $isCategoryActive = request()->routeIs('products.index')
                && (string) request('category_id') === (string) $cat->id;
                @endphp
                <li class="nav-item">
                    <a href="{{ $categoryUrl }}"
                        class="{{ $isCategoryActive ? 'active' : '' }}">
                        {{ $cat->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </nav>
    </div>
</div>

{{-- Mobile nav overlay --}}
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>

</div>{{-- end header-main --}}

<style>
    /* ====== SITE HEADER (chứa header-middle, sticky) ====== */
    .site-header {
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
    }

    /* TOP BAR & HEADER MAIN — cuộn theo trang */
    .header-topbar,
    .header-main {
        position: static;
    }

    /* TOP BAR */
    .header-topbar {
        background: #1a237e;
        color: #fff;
        font-size: 12.5px;
        padding: 6px 0;
    }

    .topbar-link {
        color: rgba(255, 255, 255, .85);
        text-decoration: none;
        font-size: 12.5px;
        transition: color .2s;
    }

    .topbar-link:hover {
        color: #fdd835;
    }

    /* HEADER MIDDLE */
    .header-middle {
        background: #fff;
        padding: 6px 0;
        border-bottom: 1px solid #137ee2;
    }

    .header-logo img {
        width: auto;
        height: 75px;
        object-fit: contain;
    }

    .search-wrap {
        display: flex;
        border: 2px solid #1565c0;
        border-radius: 25px;
        overflow: hidden;
        background: #f5f7ff;
        height: 40px;
    }

    .header-search-wrap {
        flex-grow: 1;
        max-width: 620px;
    }

    .search-wrap input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 9px 16px;
        font-size: 14px;
        outline: none;
    }

    .search-wrap button {
        background: #1565c0;
        border: none;
        color: #fff;
        padding: 0 20px;
        font-size: 16px;
        cursor: pointer;
        transition: background .2s;
    }

    .search-wrap button:hover {
        background: #0d47a1;
    }

    /* Account dropdown */
    .account-dropdown {
        position: relative;
    }

    .account-dropdown::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        height: 12px;
    }

    .account-btn {
        background: none;
        border: 1.5px solid #1565c0;
        color: #1565c0;
        border-radius: 20px;
        padding: 7px 14px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all .2s;
    }

    .account-btn:hover {
        background: #1565c0;
        color: #fff;
    }

    .account-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        min-width: 180px;
        list-style: none;
        padding: 8px 0;
        margin: 0;
        z-index: 999;
    }

    .account-dropdown:hover .account-menu {
        display: block;
    }

    .account-menu li a,
    .account-menu li button {
        display: block;
        width: 100%;
        padding: 9px 16px;
        color: #333;
        text-decoration: none;
        font-size: 13.5px;
        background: none;
        border: none;
        text-align: left;
        cursor: pointer;
        transition: background .15s;
    }

    .account-menu li a:hover,
    .account-menu li button:hover {
        background: #f5f7ff;
        color: #1565c0;
    }

    .account-menu .divider {
        border-top: 1px solid #eee;
        margin: 4px 0;
    }

    .header-login-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 10px 18px;
        height: 46px;

        background: #fff;
        color: #1565c0;

        border: 1.5px solid #1565c0;
        border-radius: 999px;

        text-decoration: none;
        font-size: 15px;
        font-weight: 500;

        transition: all .2s;
    }

    .header-login-btn i {
        font-size: 20px;
    }

    .header-login-btn:hover {
        background: #1565c0;
        color: #fff;
        border-color: #1565c0;
    }

    /* Cart */
    .cart-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 10px 18px;
        height: 46px;

        background: #fff !important;
        border: 1.5px solid #1565c0;
        border-radius: 999px;

        color: #1565c0 !important;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;

        transition: .2s;
    }

    .cart-btn i {
        font-size: 20px;
    }

    .cart-btn:hover {
        background: #1565c0 !important;
        color: #fff !important;
        border-color: #1565c0;
    }

    .cart-text {
        white-space: nowrap;
    }

    /* HEADER MAIN */
    .header-main {
        background: #1565c0;
    }

    .main-nav-list {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 2px;
    }

    .main-nav-list .nav-item>a {
        display: block;
        color: rgba(255, 255, 255, .92);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 500;
        padding: 12px 16px;
        transition: background .15s, color .15s;
        white-space: nowrap;
    }

    .main-nav-list .nav-item>a:hover,
    .main-nav-list .nav-item>a.active {
        background: rgba(255, 255, 255, .15);
        color: #fdd835;
    }

    /* Dropdown nav */
    .nav-item.has-dropdown {
        position: relative;
    }

    .dropdown-menu-nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .14);
        list-style: none;
        padding: 8px 0;
        margin: 0;
        min-width: 180px;
        z-index: 999;
    }

    .nav-item.has-dropdown:hover .dropdown-menu-nav {
        display: block;
    }

    .dropdown-menu-nav li a {
        display: flex;
        align-items: center;
        padding: 9px 16px;
        color: #333;
        text-decoration: none;
        font-size: 13.5px;
        transition: background .15s;
    }

    .dropdown-menu-nav li a:hover {
        background: #f5f7ff;
        color: #1565c0;
    }

    /* Mobile toggle */
    .mobile-nav-toggle {
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 4px;
    }

    .mobile-nav-toggle span {
        display: block;
        width: 24px;
        height: 2px;
        background: #fff;
        border-radius: 2px;
        transition: all .3s;
    }

    .mobile-nav-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .4);
        z-index: 998;
    }

    /* Mobile responsive */
    @media (max-width: 991px) {
        .main-nav-list {
            display: none;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: #1565c0;
            z-index: 999;
            overflow-y: auto;
            padding: 20px 0;
            gap: 0;
        }

        .main-nav-list.open {
            display: flex;
        }

        .main-nav-list .nav-item>a {
            padding: 13px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .dropdown-menu-nav {
            position: static;
            display: block;
            background: rgba(0, 0, 0, .15);
            box-shadow: none;
            border-radius: 0;
        }

        .dropdown-menu-nav li a {
            color: rgba(255, 255, 255, .85);
            padding-left: 32px;
        }

        .dropdown-menu-nav li a:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .mobile-nav-overlay.open {
            display: block;
        }
    }
</style>

<script>
    (function() {
        // Mobile nav
        const toggle = document.getElementById('mobileNavToggle');
        const nav = document.getElementById('mainNavList');
        const overlay = document.getElementById('mobileNavOverlay');
        if (toggle) {
            function openNav() {
                nav.classList.add('open');
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeNav() {
                nav.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
            toggle.addEventListener('click', () => nav.classList.contains('open') ? closeNav() : openNav());
            overlay.addEventListener('click', closeNav);
        }

        // Search filter panel
        const filterBtn = document.getElementById('searchFilterToggle');
        const filterPanel = document.getElementById('searchFilterPanel');
        const form = document.getElementById('headerSearchForm');

        if (filterBtn && filterPanel) {
            filterBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            });

            // Đóng khi click ngoài
            document.addEventListener('click', function(e) {
                if (!filterPanel.contains(e.target) && e.target !== filterBtn) {
                    filterPanel.style.display = 'none';
                }
            });

            // Sync giá trị filter vào hidden inputs trước submit
            form.addEventListener('submit', function() {
                document.getElementById('hsColor').value = document.getElementById('fsColor').value;
                document.getElementById('hsStorage').value = document.getElementById('fsStorage').value;
                document.getElementById('hsPriceMin').value = document.getElementById('fsPriceMin').value;
                document.getElementById('hsPriceMax').value = document.getElementById('fsPriceMax').value;
            });

            // Xóa lọc
            document.getElementById('fsClear').addEventListener('click', function() {
                document.getElementById('fsColor').value = '';
                document.getElementById('fsStorage').value = '';
                document.getElementById('fsPriceMin').value = '';
                document.getElementById('fsPriceMax').value = '';
                document.getElementById('hsColor').value = '';
                document.getElementById('hsStorage').value = '';
                document.getElementById('hsPriceMin').value = '';
                document.getElementById('hsPriceMax').value = '';
                document.getElementById('headerSearchInput').value = '';
                form.submit();
            });
        }
    })();
</script>