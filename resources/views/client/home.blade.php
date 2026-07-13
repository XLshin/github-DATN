@extends('layouts.app')

@section('title', 'Trang chủ — Byte Zone Store')

@section('content')
<div class="container py-3">
<div class="row g-4">

{{-- ===================== SIDEBAR FILTER (sticky trái) ===================== --}}
<div class="col-lg-3 d-none d-lg-block">
    <div style="position:sticky;top:80px;max-height:calc(100vh - 100px);overflow-y:auto;">
        <form method="GET" action="{{ route('home') }}" id="filterForm">

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
                            class="text-decoration-none small {{ !request('category_id') ? 'fw-semibold text-primary' : 'text-dark' }}">Tất cả</a>
                        @foreach($allCategories as $cat)
                        <a href="{{ request()->fullUrlWithQuery(['category_id' => $cat->id, 'page' => 1]) }}#tat-ca-san-pham"
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
                            class="text-decoration-none small {{ !request('brand_id') ? 'fw-semibold text-primary' : 'text-dark' }}">Tất cả</a>
                        @foreach($allBrands as $br)
                        <a href="{{ request()->fullUrlWithQuery(['brand_id' => $br->id, 'page' => 1]) }}#tat-ca-san-pham"
                            class="text-decoration-none small {{ request('brand_id') == $br->id ? 'fw-semibold text-primary' : 'text-dark' }}">
                            {{ $br->name }}
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
                        <input class="form-check-input" type="checkbox" name="in_stock" value="1"
                            id="inStock" @checked(request('in_stock')) onchange="this.form.submit()">
                        <label class="form-check-label small" for="inStock">Chỉ hiện còn hàng</label>
                    </div>
                </div>
            </div>

            @foreach(request()->except(['price_min','price_max','search','color','storage','in_stock','_token','page']) as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">Xóa bộ lọc</a>
            </div>
        </form>
    </div>
</div>

{{-- ===================== NỘI DUNG CHÍNH (phải) ===================== --}}
<div class="col-lg-9">

    {{-- BANNER --}}
    @if($banners->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div id="homeBanner" class="carousel slide rounded-3 overflow-hidden" data-bs-ride="carousel" data-bs-interval="3500">
                <div class="carousel-indicators">
                    @foreach($banners as $i => $banner)
                    <button type="button" data-bs-target="#homeBanner" data-bs-slide-to="{{ $i }}"
                        class="{{ $i === 0 ? 'active' : '' }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($banners as $i => $banner)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        @if($banner->link)<a href="{{ $banner->link }}">@endif
                        <img src="{{ asset('storage/' . $banner->image) }}"
                            class="d-block w-100" style="height:300px;object-fit:cover;" alt="{{ $banner->title }}">
                        @if($banner->link)</a>@endif
                    </div>
                    @endforeach
                </div>
                @if($banners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#homeBanner" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeBanner" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                @endif
            </div>
        </div>
        <div class="col-md-4 d-flex flex-column gap-3">
            @foreach($banners->take(2) as $banner)
            <div class="rounded-3 overflow-hidden flex-fill">
                @if($banner->link)<a href="{{ $banner->link }}">@endif
                <img src="{{ asset('storage/' . $banner->image) }}"
                    class="w-100 h-100" style="object-fit:cover;max-height:145px;" alt="{{ $banner->title }}">
                @if($banner->link)</a>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- DANH MỤC --}}
    @if($categories->isNotEmpty())
    <div class="mb-4">
        <h2 class="h6 fw-bold mb-3">Danh mục sản phẩm</h2>
        @php $catIcons = ['Điện thoại'=>'📱','Máy tính bảng'=>'💻','Phụ kiện'=>'🎧','Đồng hồ'=>'⌚']; @endphp
        <div class="row g-2">
            @foreach($categories as $cat)
            <div class="col-6 col-md-3">
                <a href="{{ route('category.products', $cat) }}"
                    class="text-decoration-none d-flex flex-column align-items-center justify-content-center p-2 bg-white border rounded-3 text-center"
                    style="min-height:80px;">
                    <span style="font-size:1.6rem;">{{ $catIcons[$cat->name] ?? '📦' }}</span>
                    <div class="fw-semibold mt-1" style="font-size:12px;">{{ $cat->name }}</div>
                    <div class="text-muted" style="font-size:10px;">{{ $cat->products_count }} sản phẩm</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ƯU ĐÃI DỊCH VỤ --}}
    <div class="row g-2 mb-4 text-center">
        @foreach([['🚚','Giao hàng nhanh','Toàn quốc 24h'],['🛡️','Bảo hành chính hãng','12-24 tháng'],['💳','Thanh toán đa dạng','COD, VNPay, MoMo'],['🔄','Đổi trả dễ dàng','7 ngày đổi trả']] as $item)
        <div class="col-6 col-md-3">
            <div class="bg-light rounded-3 p-2">
                <div style="font-size:1.4rem;">{{ $item[0] }}</div>
                <div class="fw-semibold" style="font-size:11px;">{{ $item[1] }}</div>
                <div class="text-muted" style="font-size:10px;">{{ $item[2] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FLASH SALE --}}
    @if($flashSaleProducts->isNotEmpty())
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <h2 class="h6 fw-bold mb-0 text-danger">⚡ Flash Sale</h2>
                <span class="badge bg-dark" id="flashCountdown">--:--:--</span>
            </div>
            <a href="#tat-ca-san-pham" class="btn btn-outline-danger btn-sm">Xem tất cả</a>
        </div>
        <div class="row g-3">
            @foreach($flashSaleProducts as $product)
                @include('client.partials.product_card', ['product' => $product])
            @endforeach
        </div>
    </div>
    @endif

    {{-- BÁN CHẠY --}}
    @if($bestSellers->isNotEmpty())
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h6 fw-bold mb-0">🔥 Sản phẩm bán chạy</h2>
            <a href="#tat-ca-san-pham" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="row g-3">
            @foreach($bestSellers as $product)
                @include('client.partials.product_card', ['product' => $product])
            @endforeach
        </div>
    </div>
    @endif

    {{-- SẢN PHẨM MỚI --}}
    @if($newProducts->isNotEmpty())
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h6 fw-bold mb-0">✨ Sản phẩm mới</h2>
            <a href="#tat-ca-san-pham" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="row g-3">
            @foreach($newProducts as $product)
                @include('client.partials.product_card', ['product' => $product])
            @endforeach
        </div>
    </div>
    @endif

    {{-- THEO TỪNG DANH MỤC --}}
    @foreach($productsByCategory as $catName => $catProducts)
    @if($catProducts->isNotEmpty())
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h6 fw-bold mb-0">{{ $catName }}</h2>
            <a href="#tat-ca-san-pham" class="btn btn-outline-primary btn-sm">Xem thêm</a>
        </div>
        <div class="row g-3">
            @foreach($catProducts as $product)
                @include('client.partials.product_card', ['product' => $product])
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    {{-- THƯƠNG HIỆU --}}
    @if($brands->isNotEmpty())
    <div class="mb-4">
        <h2 class="h6 fw-bold mb-3 text-center">Thương hiệu nổi bật</h2>
        <div class="row g-2 align-items-center justify-content-center">
            @foreach($brands as $brand)
            <div class="col-4 col-md-2">
                <a href="{{ route('brand.products', $brand) }}"
                    class="d-flex align-items-center justify-content-center p-2 bg-white border rounded-3"
                    style="min-height:60px;">
                    @if($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}"
                        style="max-height:36px;max-width:100%;object-fit:contain;">
                    @else
                    <span class="fw-bold text-dark small">{{ $brand->name }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- TẤT CẢ SẢN PHẨM --}}
    <div id="tat-ca-san-pham" class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h2 class="h6 fw-bold mb-0">Tất cả sản phẩm
                @if($allProducts->total())
                <span class="text-muted fw-normal">({{ $allProducts->total() }})</span>
                @endif
            </h2>
            <select class="form-select form-select-sm" style="width:auto"
                onchange="location.href='{{ route('home') }}?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)), sort: this.value, page: 1}).toString() + '#tat-ca-san-pham'">
                <option value="latest"      @selected(request('sort','latest')==='latest')>Mới nhất</option>
                <option value="price_asc"   @selected(request('sort')==='price_asc')>Giá tăng dần</option>
                <option value="price_desc"  @selected(request('sort')==='price_desc')>Giá giảm dần</option>
                <option value="best_seller" @selected(request('sort')==='best_seller')>Bán chạy</option>
            </select>
        </div>

        <div class="row g-3">
            @forelse($allProducts as $product)
                @include('client.partials.product_card', ['product' => $product])
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="lni lni-empty-file fs-1 d-block mb-2"></i>
                Không tìm thấy sản phẩm.
                <div class="mt-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">Xóa bộ lọc</a>
                </div>
            </div>
            @endforelse
        </div>

        @if($allProducts->hasPages())
        <div class="mt-4">{{ $allProducts->withQueryString()->links() }}</div>
        @endif
    </div>

</div>{{-- end col-lg-9 --}}
</div>{{-- end row --}}
</div>{{-- end container --}}

@endsection

@push('scripts')
<script>
(function() {
    const el = document.getElementById('flashCountdown');
    if (!el) return;
    const end = new Date();
    end.setHours(23, 59, 59, 0);
    function tick() {
        const diff = end - new Date();
        if (diff <= 0) { el.textContent = '00:00:00'; return; }
        const h = String(Math.floor(diff/3600000)).padStart(2,'0');
        const m = String(Math.floor((diff%3600000)/60000)).padStart(2,'0');
        const s = String(Math.floor((diff%60000)/1000)).padStart(2,'0');
        el.textContent = `${h}:${m}:${s}`;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
