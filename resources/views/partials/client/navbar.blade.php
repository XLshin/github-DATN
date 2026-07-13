@php
    $categories = \App\Models\Category::orderBy('name')->get();
    $brands = \App\Models\Brand::orderBy('name')->get();
    $colors = \App\Models\ProductVariant::query()
        ->whereNotNull('color')
        ->where('color', '<>', '')
        ->distinct()
        ->orderBy('color')
        ->pluck('color');
    $storages = \App\Models\Product::query()
        ->whereNotNull('storage')
        ->where('storage', '<>', '')
        ->distinct()
        ->orderBy('storage')
        ->pluck('storage');
    $user = auth()->user();
    $isCustomer = $user?->role === 'customer';
    $isAdminUser = $user && in_array($user->role, ['admin', 'staff'], true);
    $cartCount = $isCustomer ? app(\App\Services\CartService::class)->getCartCount($user) : 0;
@endphp

<div class="header-topbar" style="padding: 0; line-height: 0;">
    <a href="{{ route('home') }}" style="display:block;">
        <img src="{{ asset('assets-client/images/banner/banner top.png') }}" alt="Banner khuyến mãi" style="width: 100%; height: auto; display: block;">
    </a>
</div>

<header class="site-header">
    <div class="header-middle">
        <div class="container">
            <div class="header-middle-inner">
                <a href="{{ route('home') }}" class="header-logo">
                    <img src="{{ asset('assets-client/images/logo/logo.png') }}" alt="ByteZone">
                </a>

                <div class="header-search-wrap">
                    <form action="{{ route('products.index') }}" method="GET" id="headerSearchForm">
                        <div class="search-wrap">
                            <input type="text" name="search" id="headerSearchInput"
                                value="{{ request('search') }}"
                                placeholder="Tìm theo tên, màu, dung lượng, giá..." autocomplete="off">
                            <input type="hidden" name="color" id="hsColor" value="{{ request('color') }}">
                            <input type="hidden" name="storage" id="hsStorage" value="{{ request('storage') }}">
                            <input type="hidden" name="price_min" id="hsPriceMin" value="{{ request('price_min') }}">
                            <input type="hidden" name="price_max" id="hsPriceMax" value="{{ request('price_max') }}">
                            <button type="button" id="searchFilterToggle" title="Lọc nâng cao" class="filter-toggle">
                                <i class="lni lni-funnel"></i>
                            </button>
                            <button type="submit" title="Tìm kiếm"><i class="lni lni-search-alt"></i></button>
                        </div>

                        <div id="searchFilterPanel" class="search-filter-panel">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label>Màu sắc</label>
                                    <select id="fsColor" class="form-select form-select-sm">
                                        <option value="">Tất cả màu</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color }}" @selected(request('color') === $color)>{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label>Dung lượng</label>
                                    <select id="fsStorage" class="form-select form-select-sm">
                                        <option value="">Tất cả</option>
                                        @foreach($storages as $storage)
                                            <option value="{{ $storage }}" @selected(request('storage') === $storage)>{{ $storage }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label>Giá từ (đ)</label>
                                    <input type="number" id="fsPriceMin" class="form-control form-control-sm" placeholder="0" value="{{ request('price_min') }}">
                                </div>
                                <div class="col-6">
                                    <label>Giá đến (đ)</label>
                                    <input type="number" id="fsPriceMax" class="form-control form-control-sm" placeholder="Không giới hạn" value="{{ request('price_max') }}">
                                </div>
                                <div class="col-12 d-flex gap-2 justify-content-end mt-1">
                                    <button type="button" id="fsClear" class="btn btn-sm btn-outline-secondary">Xóa lọc</button>
                                    <button type="submit" id="fsApply" class="btn btn-sm btn-primary">Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="header-actions">
                    <div class="nav-hotline d-none d-xl-flex">
                        <i class="lni lni-phone"></i>
                        <span>0909 999 888</span>
                    </div>

                    <div class="account-dropdown">
                        @auth
                            <button type="button" class="account-btn">
                                <i class="lni lni-user"></i>
                                <span class="d-none d-lg-inline">{{ \Illuminate\Support\Str::limit($user->name, 12) }}</span>
                                <i class="lni lni-chevron-down account-chevron"></i>
                            </button>
                            <ul class="account-menu">
                                @if($isCustomer)
                                    <li><a href="{{ route('dashboard') }}"><i class="lni lni-user me-2"></i>Tài khoản</a></li>
                                    <li><a href="{{ route('orders.index') }}"><i class="lni lni-package me-2"></i>Đơn hàng</a></li>
                                    <li><a href="{{ route('points.index') }}"><i class="lni lni-star me-2"></i>Điểm tích lũy</a></li>
                                @elseif($isAdminUser)
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
                        @else
                            <a href="{{ route('login') }}" class="header-login-btn">
                                <i class="lni lni-user"></i>
                                <span class="d-none d-lg-inline">Đăng nhập</span>
                            </a>
                        @endauth
                    </div>

                    @auth
                        @if($isCustomer)
                            <a href="{{ route('cart.index') }}" class="cart-btn">
                                <i class="lni lni-cart" id="cart-fly-target"></i>
                                <span class="cart-text">Giỏ hàng</span>
                                <span class="cart-badge" id="nav-cart-count">{{ $cartCount }}</span>
                            </a>
                        @elseif($isAdminUser)
                            <a href="{{ route('admin.dashboard') }}" class="cart-btn">
                                <i class="lni lni-dashboard"></i>
                                <span class="cart-text">Quản trị</span>
                            </a>
                        @endif
                    @else
                    <a href="{{ route('login') }}" class="cart-btn">
                        <i class="lni lni-cart"></i>
                        <span class="cart-text">Giỏ hàng</span>
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="header-main">
        <div class="container">
            <nav class="main-nav">
                <button class="mobile-nav-toggle d-lg-none" id="mobileNavToggle" type="button" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>

                <ul class="main-nav-list" id="mainNavList">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Trang chủ</a>
                    </li>

                    @foreach($categories as $category)
                        @php
                            $categoryUrl = route('products.index', ['category_id' => $category->id]);
                            $isCategoryActive = request()->routeIs('products.index')
                                && (string) request('category_id') === (string) $category->id;
                        @endphp
                        <li class="nav-item">
                            <a href="{{ $categoryUrl }}" class="{{ $isCategoryActive ? 'active' : '' }}">{{ $category->name }}</a>
                        </li>
                    @endforeach

                    <li class="nav-item has-dropdown">
                        <a href="javascript:void(0)" class="{{ request()->routeIs('brand.*') ? 'active' : '' }}">
                            Thương hiệu <i class="lni lni-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu-nav">
                            @foreach($brands as $brand)
                                <li><a href="{{ route('brand.products', $brand) }}">{{ $brand->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>

                    @auth
                        @if($isCustomer)
                            <li class="nav-item d-lg-none"><a href="{{ route('cart.index') }}">Giỏ hàng</a></li>
                            <li class="nav-item d-lg-none"><a href="{{ route('orders.index') }}">Đơn hàng</a></li>
                            <li class="nav-item d-lg-none"><a href="{{ route('dashboard') }}">Tài khoản</a></li>
                        @elseif($isAdminUser)
                            <li class="nav-item d-lg-none"><a href="{{ route('admin.dashboard') }}">Quản trị</a></li>
                        @endif
                    @else
                        <li class="nav-item d-lg-none"><a href="{{ route('login') }}">Đăng nhập</a></li>
                        <li class="nav-item d-lg-none"><a href="{{ route('register') }}">Đăng ký</a></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </div>
</header>

<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>

<style>
.site-header { position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
.header-topbar, .header-main { position: static; }
.header-middle { background: #fff; padding: 6px 0; border-bottom: 1px solid #137ee2; }
.header-middle-inner { display: flex; align-items: center; gap: 24px; }
.header-logo { flex: 0 0 auto; }
.header-logo img { width: auto; height: 75px; object-fit: contain; }
.header-search-wrap { position: relative; flex: 1 1 auto; max-width: 620px; }
.search-wrap { display: flex; border: 2px solid #1565c0; border-radius: 25px; overflow: hidden; background: #f5f7ff; height: 40px; }
.search-wrap input { flex: 1; border: none; background: transparent; padding: 9px 16px; font-size: 14px; outline: none; min-width: 0; }
.search-wrap button { background: #1565c0; border: none; color: #fff; padding: 0 18px; font-size: 16px; cursor: pointer; transition: background .2s; }
.search-wrap button:hover { background: #0d47a1; }
.search-wrap .filter-toggle { background: transparent; color: #1565c0; padding: 0 10px; }
.search-filter-panel { display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1.5px solid #1565c0; border-radius: 12px; padding: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.12); z-index: 1050; }
.search-filter-panel label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; display: block; }
.header-actions { display: flex; align-items: center; gap: 12px; flex: 0 0 auto; }
.nav-hotline { align-items: center; gap: 8px; color: #1565c0; font-weight: 600; white-space: nowrap; }
.account-dropdown { position: relative; }
.account-dropdown::after { content: ""; position: absolute; left: 0; right: 0; top: 100%; height: 12px; }
.account-btn, .header-login-btn, .cart-btn { display: inline-flex; align-items: center; gap: 8px; height: 46px; padding: 10px 18px; background: #fff; color: #1565c0; border: 1.5px solid #1565c0; border-radius: 999px; text-decoration: none; font-size: 15px; font-weight: 500; transition: all .2s; cursor: pointer; white-space: nowrap; }
.account-btn:hover, .header-login-btn:hover, .cart-btn:hover { background: #1565c0; color: #fff; border-color: #1565c0; }
.account-btn i, .header-login-btn i, .cart-btn i { font-size: 20px; }
.account-chevron { font-size: 10px !important; }
.account-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); background: #fff; border: 1px solid #e0e0e0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12); min-width: 190px; list-style: none; padding: 8px 0; margin: 0; z-index: 1051; }
.account-dropdown:hover .account-menu { display: block; }
.account-menu li a, .account-menu li button { display: block; width: 100%; padding: 9px 16px; color: #333; text-decoration: none; font-size: 13.5px; background: none; border: none; text-align: left; cursor: pointer; transition: background .15s; }
.account-menu li a:hover, .account-menu li button:hover { background: #f5f7ff; color: #1565c0; }
.account-menu .divider { border-top: 1px solid #eee; margin: 4px 0; }
.cart-btn { position: relative; }
.cart-badge { position: absolute; top: -7px; right: -7px; min-width: 20px; height: 20px; border-radius: 999px; background: #e53935; color: #fff; font-size: 12px; line-height: 20px; text-align: center; padding: 0 5px; }
.header-main { background: #1565c0; }
.main-nav { display: flex; align-items: center; justify-content: center; min-height: 46px; }
.main-nav-list { display: flex; align-items: center; list-style: none; margin: 0; padding: 0; gap: 2px; }
.main-nav-list .nav-item > a { display: block; color: rgba(255,255,255,.92); text-decoration: none; font-size: 13.5px; font-weight: 500; padding: 12px 16px; transition: background .15s, color .15s; white-space: nowrap; }
.main-nav-list .nav-item > a:hover, .main-nav-list .nav-item > a.active { background: rgba(255,255,255,.15); color: #fdd835; }
.nav-item.has-dropdown { position: relative; }
.dropdown-menu-nav { display: none; position: absolute; top: 100%; left: 0; background: #fff; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.14); list-style: none; padding: 8px 0; margin: 0; min-width: 180px; z-index: 999; }
.nav-item.has-dropdown:hover .dropdown-menu-nav { display: block; }
.dropdown-menu-nav li a { display: flex; align-items: center; padding: 9px 16px; color: #333; text-decoration: none; font-size: 13.5px; transition: background .15s; }
.dropdown-menu-nav li a:hover { background: #f5f7ff; color: #1565c0; }
.mobile-nav-toggle { background: none; border: none; cursor: pointer; display: flex; flex-direction: column; gap: 5px; padding: 4px; }
.mobile-nav-toggle span { display: block; width: 24px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s; }
.mobile-nav-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 998; }
@media (max-width: 991px) {
    .header-middle-inner { gap: 12px; flex-wrap: wrap; }
    .header-logo img { height: 58px; }
    .header-search-wrap { order: 3; flex-basis: 100%; max-width: none; }
    .header-actions { margin-left: auto; gap: 8px; }
    .cart-text { display: none; }
    .main-nav { justify-content: flex-start; }
    .main-nav-list { display: none; flex-direction: column; align-items: stretch; position: fixed; top: 0; left: 0; width: 280px; height: 100vh; background: #1565c0; z-index: 999; overflow-y: auto; padding: 20px 0; gap: 0; }
    .main-nav-list.open { display: flex; }
    .main-nav-list .nav-item > a { padding: 13px 20px; border-bottom: 1px solid rgba(255,255,255,.1); }
    .dropdown-menu-nav { position: static; display: block; background: rgba(0,0,0,.15); box-shadow: none; border-radius: 0; }
    .dropdown-menu-nav li a { color: rgba(255,255,255,.85); padding-left: 32px; }
    .dropdown-menu-nav li a:hover { background: rgba(255,255,255,.1); color: #fff; }
    .mobile-nav-overlay.open { display: block; }
}
</style>

<script>
(function () {
    const toggle = document.getElementById('mobileNavToggle');
    const nav = document.getElementById('mainNavList');
    const overlay = document.getElementById('mobileNavOverlay');

    if (toggle && nav && overlay) {
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

        toggle.addEventListener('click', function () {
            nav.classList.contains('open') ? closeNav() : openNav();
        });
        overlay.addEventListener('click', closeNav);
    }

    const filterBtn = document.getElementById('searchFilterToggle');
    const filterPanel = document.getElementById('searchFilterPanel');
    const form = document.getElementById('headerSearchForm');

    if (filterBtn && filterPanel && form) {
        filterBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            filterPanel.style.display = filterPanel.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function (event) {
            if (!filterPanel.contains(event.target) && !filterBtn.contains(event.target)) {
                filterPanel.style.display = 'none';
            }

        form.addEventListener('submit', function () {
            document.getElementById('hsColor').value = document.getElementById('fsColor').value;
            document.getElementById('hsStorage').value = document.getElementById('fsStorage').value;
            document.getElementById('hsPriceMin').value = document.getElementById('fsPriceMin').value;
            document.getElementById('hsPriceMax').value = document.getElementById('fsPriceMax').value;
        });

        document.getElementById('fsClear')?.addEventListener('click', function () {
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
