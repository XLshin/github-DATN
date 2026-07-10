<header class="header navbar-area">
    {{-- Topbar --}}
    <div class="topbar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-4 col-12">
                    {{-- Có thể để trống hoặc thêm thông tin phụ --}}
                </div>

                <div class="col-lg-4 col-md-4 col-12">
                    <div class="top-middle">
                        <ul class="useful-links">
                            <li>
                                <a href="{{ route('home') }}">Trang chủ</a>
                            </li>

                            @guest
                            <li>
                                <a href="{{ route('login') }}">Giỏ hàng</a>
                            </li>
                            @endguest

                            @auth
                            @if (auth()->user()->role === 'customer')
                            <li>
                                <a href="{{ route('cart.index') }}">Giỏ hàng</a>
                            </li>

                            <li>
                                <a href="{{ route('orders.index') }}">Đơn hàng</a>
                            </li>
                            @elseif (in_array(auth()->user()->role, ['admin', 'staff'], true))
                            <li>
                                <a href="{{ route('admin.dashboard') }}">Trang quản trị</a>
                            </li>
                            @endif
                            @endauth
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-12">
                    <div class="top-end">
                        @auth
                        <div class="user">
                            <i class="lni lni-user"></i>
                            {{ auth()->user()->name ?? 'Tài khoản' }}
                        </div>

                        <ul class="user-login">
                            @if (auth()->user()->role === 'customer')
                            <li>
                                <a href="{{ route('dashboard') }}">Tài khoản</a>
                            </li>
                            @elseif (in_array(auth()->user()->role, ['admin', 'staff'], true))
                            <li>
                                <a href="{{ route('admin.dashboard') }}">Quản trị</a>
                            </li>
                            @endif

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        style="border: 0; background: transparent; color: inherit; padding: 0;">
                                        Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                        @else
                        <div class="user">
                            <i class="lni lni-user"></i>
                            Xin chào
                        </div>

                        <ul class="user-login">
                            <li>
                                <a href="{{ route('login') }}">Đăng nhập</a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}">Đăng ký</a>
                            </li>
                        </ul>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Header middle --}}
    <div class="header-middle">
        <div class="container">
            <div class="row align-items-center">
                {{-- Logo --}}
                <div class="col-lg-3 col-md-3 col-7">
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="{{ asset('assets-client/images/logo/logo.svg') }}" alt="Byte Zone Store">
                    </a>
                </div>

                {{-- Search --}}
                <div class="col-lg-5 col-md-7 d-xs-none">
                    <div class="main-menu-search">
                        <div class="navbar-search search-style-5">
                            <div class="search-select">
                                <div class="select-position">
                                    <select>
                                        <option selected>Tất cả</option>
                                        <option>iPhone</option>
                                        <option>Samsung</option>
                                        <option>Xiaomi</option>
                                        <option>OPPO</option>
                                        <option>Phụ kiện</option>
                                    </select>
                                </div>
                            </div>

                            <div class="search-input">
                                <input type="text" placeholder="Tìm điện thoại, phụ kiện...">
                            </div>

                            <div class="search-btn">
                                <button type="button">
                                    <i class="lni lni-search-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hotline + Cart/Admin --}}
                <div class="col-lg-4 col-md-2 col-5">
                    <div class="middle-right-area">
                        <div class="nav-hotline">
                            <i class="lni lni-phone"></i>
                            <h3>
                                Hotline:
                                <span>0909 999 888</span>
                            </h3>
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
                                    <i class="lni lni-cart"></i>
                                    <span class="total-items" id="nav-cart-count">{{ app(\App\Services\CartService::class)->getCount(auth()->user()) }}</span>
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

    {{-- Header bottom --}}
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-6 col-12">
                <div class="nav-inner">
                    {{-- Category menu --}}
                    <div class="mega-category-menu">
                        <span class="cat-button">
                            <i class="lni lni-menu"></i>
                            Danh mục
                        </span>

                        <ul class="sub-category">
                            <li>
                                <a href="javascript:void(0)">
                                    Điện thoại
                                    <i class="lni lni-chevron-right"></i>
                                </a>

                                <ul class="inner-sub-category">
                                    <li><a href="javascript:void(0)">iPhone</a></li>
                                    <li><a href="javascript:void(0)">Samsung</a></li>
                                    <li><a href="javascript:void(0)">Xiaomi</a></li>
                                    <li><a href="javascript:void(0)">OPPO</a></li>
                                    <li><a href="javascript:void(0)">Vivo</a></li>
                                </ul>
                            </li>

                            <li><a href="javascript:void(0)">Máy tính bảng</a></li>
                            <li><a href="javascript:void(0)">Đồng hồ thông minh</a></li>
                            <li><a href="javascript:void(0)">Tai nghe</a></li>
                            <li><a href="javascript:void(0)">Sạc, cáp</a></li>
                            <li><a href="javascript:void(0)">Ốp lưng</a></li>
                            <li><a href="javascript:void(0)">Khuyến mãi</a></li>
                        </ul>
                    </div>

                    {{-- Main menu --}}
                    <nav class="navbar navbar-expand-lg">
                        <button
                            class="navbar-toggler mobile-menu-btn"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#clientNavbar"
                            aria-controls="clientNavbar"
                            aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="toggler-icon"></span>
                            <span class="toggler-icon"></span>
                            <span class="toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse sub-menu-bar" id="clientNavbar">
                            <ul id="nav" class="navbar-nav ms-auto">
                                <li class="nav-item">
                                    <a
                                        href="{{ route('home') }}"
                                        class="{{ request()->routeIs('home') ? 'active' : '' }}">
                                        Trang chủ
                                    </a>
                                </li>

                                @guest
                                <li class="nav-item">
                                    <a href="{{ route('login') }}">
                                        Giỏ hàng
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('login') }}">
                                        Đăng nhập
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('register') }}">
                                        Đăng ký
                                    </a>
                                </li>
                                @endguest

                                @auth
                                @if (auth()->user()->role === 'customer')
                                <li class="nav-item">
                                    <a
                                        href="{{ route('cart.index') }}"
                                        class="{{ request()->routeIs('cart.*') ? 'active' : '' }}">
                                        Giỏ hàng
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a
                                        href="{{ route('orders.index') }}"
                                        class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">
                                        Đơn hàng
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a
                                        href="{{ route('points.index') }}"
                                        class="{{ request()->routeIs('points.*') ? 'active' : '' }}">
                                        Điểm tích lũy
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a
                                        href="{{ route('dashboard') }}"
                                        class="{{ request()->routeIs('dashboard', 'profile.*', 'password.change*') ? 'active' : '' }}">
                                        Tài khoản
                                    </a>
                                </li>
                                @elseif (in_array(auth()->user()->role, ['admin', 'staff'], true))
                                <li class="nav-item">
                                    <a href="{{ route('admin.dashboard') }}" class="text-warning">
                                        Trang quản trị
                                    </a>
                                </li>
                                @endif
                                @endauth
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="nav-social">
                    <h5 class="title">Theo dõi:</h5>

                    <ul>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="lni lni-facebook-filled"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="lni lni-instagram"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="lni lni-youtube"></i>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="lni lni-twitter-original"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>