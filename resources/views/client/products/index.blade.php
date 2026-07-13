@extends('layouts.app')

@section('title', 'Sản phẩm')

@section('content')
<div class="product-listing-page py-4 py-lg-5">
<div class="container">
    <div class="product-listing-hero mb-4">
        <div>
            <span class="product-listing-eyebrow">Khám phá cửa hàng</span>
            <h1 class="mb-1">Sản phẩm</h1>
            <p class="mb-0">Chọn sản phẩm phù hợp với nhu cầu của bạn.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('home') }}" class="btn product-home-button">
                <i class="lni lni-home me-1"></i> Về trang chủ
            </a>
            <div class="product-listing-hero-icon" aria-hidden="true"><i class="lni lni-shopping-basket"></i></div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Sidebar --}}
        <div class="col-lg-3">
            <form method="GET" action="{{ route('products.index') }}" id="filterForm">

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Tìm kiếm</h6>
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Tên sản phẩm...">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="lni lni-search-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Danh mục</h6>
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ request()->fullUrlWithQuery(['category_id' => '', 'page' => 1]) }}"
                                class="text-decoration-none small {{ !request('category_id') ? 'fw-semibold text-primary' : 'text-dark' }}">
                                Tất cả
                            </a>
                            @foreach($categories as $cat)
                            <a href="{{ request()->fullUrlWithQuery(['category_id' => $cat->id, 'page' => 1]) }}"
                                class="text-decoration-none small {{ request('category_id') == $cat->id ? 'fw-semibold text-primary' : 'text-dark' }}">
                                {{ $cat->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Thương hiệu</h6>
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ request()->fullUrlWithQuery(['brand_id' => '', 'page' => 1]) }}"
                                class="text-decoration-none small {{ !request('brand_id') ? 'fw-semibold text-primary' : 'text-dark' }}">
                                Tất cả
                            </a>
                            @foreach($brands as $brand)
                            <a href="{{ request()->fullUrlWithQuery(['brand_id' => $brand->id, 'page' => 1]) }}"
                                class="text-decoration-none small {{ request('brand_id') == $brand->id ? 'fw-semibold text-primary' : 'text-dark' }}">
                                {{ $brand->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Khoảng giá</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="number" name="price_min" value="{{ request('price_min') }}"
                                class="form-control form-control-sm" placeholder="Từ">
                            <span class="text-muted small">—</span>
                            <input type="number" name="price_max" value="{{ request('price_max') }}"
                                class="form-control form-control-sm" placeholder="Đến">
                        </div>
                    </div>
                </div>

                @if($colors->count())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Màu sắc</h6>
                        <select name="color" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            @foreach($colors as $color)
                            <option value="{{ $color }}" @selected(request('color') === $color)>{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if($storages->count())
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2">Dung lượng</h6>
                        <select name="storage" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            @foreach($storages as $storage)
                            <option value="{{ $storage }}" @selected(request('storage') === $storage)>{{ $storage }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStock"
                                @checked(request('in_stock')) onchange="this.form.submit()">
                            <label class="form-check-label small" for="inStock">Chỉ hiện còn hàng</label>
                        </div>
                    </div>
                </div>

                {{-- Giữ các param khi submit form giá --}}
                @foreach(request()->except(['price_min','price_max','search','color','storage','in_stock','_token','page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">Xóa bộ lọc</a>
                </div>
            </form>
        </div>

        {{-- Danh sách --}}
        <div class="col-lg-9">
            <div class="product-toolbar mb-3">
                <p class="mb-0 text-muted small product-result-count">
                    @if($products->total())
                        Hiển thị <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong> / <strong>{{ $products->total() }}</strong> sản phẩm
                    @else
                        Không tìm thấy sản phẩm
                    @endif
                </p>
                <select class="form-select form-select-sm product-sort-select"
                    onchange="location.href='{{ route('products.index') }}?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)), sort: this.value, page: 1}).toString()">
                    <option value="latest"      @selected(request('sort','latest') === 'latest')>Mới nhất</option>
                    <option value="price_asc"   @selected(request('sort') === 'price_asc')>Giá tăng dần</option>
                    <option value="price_desc"  @selected(request('sort') === 'price_desc')>Giá giảm dần</option>
                    <option value="best_seller" @selected(request('sort') === 'best_seller')>Bán chạy</option>
                </select>
            </div>

            <div class="row g-3">
                @forelse($products as $product)
                    @include('client.partials.product_card', ['product' => $product])
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="lni lni-empty-file fs-1 d-block mb-3"></i>
                        Không tìm thấy sản phẩm nào.
                        <div class="mt-2">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Xóa bộ lọc</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
            <div class="mt-3">{{ $products->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
</div>

<style>
    .product-listing-page { background: #f6f8fc; min-height: 70vh; }
    .product-listing-hero { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; padding: 1.5rem 1.75rem; border-radius: 20px; color: #fff; background: linear-gradient(120deg, #0b63ce, #174fa5); box-shadow: 0 14px 32px rgba(16, 77, 163, .18); }
    .product-listing-hero h1 { font-size: clamp(1.55rem, 3vw, 2rem); font-weight: 700; }
    .product-listing-hero p { color: rgba(255,255,255,.82); }
    .product-listing-eyebrow { display: block; margin-bottom: .25rem; color: #cfe4ff; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
    .product-listing-hero-icon { width: 58px; height: 58px; display: grid; place-items: center; flex: 0 0 auto; border: 1px solid rgba(255,255,255,.28); border-radius: 16px; background: rgba(255,255,255,.13); font-size: 1.65rem; }
    .product-home-button { color: #fff; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.1); font-size: .88rem; font-weight: 600; white-space: nowrap; }
    .product-home-button:hover { color: #145ab6; border-color: #fff; background: #fff; }
    .product-listing-page .card { border: 1px solid #e8edf5 !important; border-radius: 14px; box-shadow: 0 4px 14px rgba(19, 43, 79, .045) !important; }
    .product-listing-page .card-body { padding: 1rem; }
    .product-listing-page .card h6 { color: #172b4d; font-size: .92rem; }
    .product-listing-page .form-control, .product-listing-page .form-select { border-color: #d8e0eb; border-radius: 9px; }
    .product-listing-page .form-control:focus, .product-listing-page .form-select:focus { border-color: #1976d2; box-shadow: 0 0 0 .2rem rgba(25,118,210,.12); }
    .product-listing-page .btn { border-radius: 9px; }
    .product-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1rem; border: 1px solid #e8edf5; border-radius: 12px; background: #fff; }
    .product-result-count strong { color: #145ab6; }
    .product-sort-select { width: auto; min-width: 145px; }
    .product-listing-page .product-card { border: 1px solid #e4eaf2; border-radius: 14px; box-shadow: 0 5px 16px rgba(15, 39, 75, .055); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .product-listing-page .product-card:hover { border-color: #bcd7f6; box-shadow: 0 13px 28px rgba(18, 88, 180, .14); transform: translateY(-5px); }
    .product-listing-page .product-card > a:first-child { height: 205px !important; padding: .75rem; background: linear-gradient(145deg, #f8fbff, #eff4fa); }
    .product-listing-page .product-card > a:first-child img { width: 100%; height: 100%; object-fit: contain; transition: transform .25s ease; }
    .product-listing-page .product-card:hover > a:first-child img { transform: scale(1.045); }
    .product-listing-page .product-card .card-body > div:first-child { min-height: 25px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .product-listing-page .product-card .badge { font-size: .67rem; font-weight: 500; }
    .product-listing-page .product-card h3 { min-height: 2.7rem; line-height: 1.35; }
    .product-listing-page .product-card h3 a { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .product-listing-page .price-tag { font-size: 1.03rem; }
    .product-listing-page .product-card .btn { font-weight: 600; }
    @media (max-width: 575.98px) { .product-listing-hero { padding: 1.2rem; } .product-listing-hero-icon { display: none; } .product-toolbar { align-items: flex-start; flex-direction: column; } .product-sort-select { width: 100%; } }
</style>
@endsection
