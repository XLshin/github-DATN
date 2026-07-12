@extends('layouts.app')

@section('title', 'Trang chủ — Byte Zone Store')

@section('content')

{{-- BANNER --}}
@if($banners->isNotEmpty())
<section class="py-3">
    <div class="container">
        <div id="homeBanner" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-indicators">
                @foreach($banners as $i => $banner)
                    <button type="button" data-bs-target="#homeBanner" data-bs-slide-to="{{ $i }}"
                        class="{{ $i === 0 ? 'active' : '' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner rounded-3 overflow-hidden">
                @foreach($banners as $i => $banner)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    @if($banner->link)
                        <a href="{{ $banner->link }}">
                    @endif
                    <img src="{{ asset('storage/' . $banner->image) }}"
                         class="d-block w-100" style="max-height:500px;object-fit:contain;background:#000"
                         alt="{{ $banner->title }}">
                    @if($banner->link)</a>@endif
                    <div class="carousel-caption d-none d-md-block">
                        <h5>{{ $banner->title }}</h5>
                    </div>
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
</section>
@endif

{{-- DANH MỤC NỔI BẬT --}}
@if($categories->isNotEmpty())
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h4 mb-0 fw-bold">Danh mục nổi bật</h2>
        </div>
        @php
            $categoryIcons = [
                'Điện thoại' => '<svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="currentColor"><path d="M16 2H8C6.9 2 6 2.9 6 4v16c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm4-3H8V5h8v12z"/></svg>',
                'Máy tính bảng' => '<svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="18" rx="3"/><line x1="7" y1="21" x2="17" y2="21"/><line x1="12" y1="18" x2="12" y2="21"/></svg>',
                'Phụ kiện' => '<svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
            ];
        @endphp
        <div class="row g-3">
            @foreach($categories as $category)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('category.products', $category) }}"
                   class="text-decoration-none d-block rounded-3 border overflow-hidden hover-shadow bg-white"
                   style="transition:.2s">
                    {{-- Icon area full width --}}
                    <div class="d-flex align-items-center justify-content-center bg-light text-primary"
                         style="height:140px">
                        {!! $categoryIcons[$category->name] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>' !!}
                    </div>
                    {{-- Label --}}
                    <div class="p-2 text-center">
                        <div class="fw-semibold">{{ $category->name }}</div>
                        <small class="text-muted">{{ $category->products_count }} sản phẩm</small>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- THƯƠNG HIỆU NỔI BẬT --}}
@if($brands->isNotEmpty())
<section class="py-4 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h4 mb-0 fw-bold">Thương hiệu nổi bật</h2>
        </div>
        <div class="row g-3 align-items-center">
            @foreach($brands as $brand)
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('brand.products', $brand) }}"
                   class="text-decoration-none d-flex align-items-center justify-content-center p-3 bg-white rounded-3 border h-100"
                   style="min-height:80px;transition:.2s">
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}"
                             alt="{{ $brand->name }}" style="max-height:48px;max-width:100%;object-fit:contain">
                    @else
                        <span class="fw-bold text-dark">{{ $brand->name }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- SẢN PHẨM MỚI --}}
@if($newProducts->isNotEmpty())
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h4 mb-0 fw-bold">Sản phẩm mới</h2>
        </div>
        <div class="row g-3">
            @foreach($newProducts as $product)
                @include('client.partials.product_card', ['product' => $product])
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="btn btn-outline-primary">Xem tất cả</a>
        </div>
    </div>
</section>
@endif

{{-- SẢN PHẨM BÁN CHẠY --}}
@if($bestSellers->isNotEmpty())
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="h4 mb-0 fw-bold">🔥 Sản phẩm bán chạy</h2>
        </div>
        <div class="row g-3">
            @foreach($bestSellers as $product)
                @include('client.partials.product_card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
