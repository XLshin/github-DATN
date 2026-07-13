@extends('layouts.app')

@section('title', 'Trang chủ — Byte Zone Store')

@section('content')
<div class="container py-3">
<div class="row g-4">

{{-- ===================== NỘI DUNG CHÍNH ===================== --}}
<div class="col-12">

    {{-- BANNER --}}
    @if($banners->isNotEmpty())
    <div class="mb-4">
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
                        class="d-block w-100" style="height:380px;object-fit:cover;" alt="{{ $banner->title }}">
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
    @endif

    {{-- DANH MỤC --}}
    @if($categories->isNotEmpty())
    <div class="mb-4">
        <h2 class="h6 fw-bold mb-3">Danh mục sản phẩm</h2>
        @php $catIcons = ['Điện thoại'=>'📱','Máy tính bảng'=>'💻','Phụ kiện'=>'🎧','Đồng hồ'=>'⌚']; @endphp
        <div class="row g-2">
            @foreach($categories as $cat)
            <div class="col-6 col-md-3">
                <a href="{{ route('products.index', ['category_id' => $cat->id]) }}"
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
                    class="d-flex flex-column align-items-center justify-content-center p-2 bg-white border rounded-3 text-decoration-none"
                    style="min-height:70px; transition:.2s;"
onmouseover="this.style.borderColor='#1565c0';this.style.boxShadow='0 2px 8px rgba(21,101,192,.15)'"
                    onmouseout="this.style.borderColor='';this.style.boxShadow=''">
                    @if($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}"
                        style="max-height:36px;max-width:90%;object-fit:contain;">
                    @else
                    <i class="lni lni-tag" style="font-size:1.4rem;color:#1565c0;"></i>
                    <span class="fw-bold text-dark mt-1" style="font-size:12px;">{{ $brand->name }}</span>
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