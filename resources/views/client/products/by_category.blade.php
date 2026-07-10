@extends('layouts.app')

@section('title', $category->name . ' — Byte Zone Store')

@section('content')
<div class="container py-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Sidebar lọc --}}
        <div class="col-lg-3">
            <div class="border rounded-3 p-3">
                <h6 class="fw-bold mb-3">Thương hiệu</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-1">
                        <a href="{{ route('category.products', $category) }}"
                           class="text-decoration-none {{ !request('brand_id') ? 'fw-bold text-primary' : 'text-dark' }}">
                            Tất cả
                        </a>
                    </li>
                    @foreach($brands as $brand)
                    <li class="mb-1">
                        <a href="{{ route('category.products', $category) }}?brand_id={{ $brand->id }}"
                           class="text-decoration-none {{ request('brand_id') == $brand->id ? 'fw-bold text-primary' : 'text-dark' }}">
                            {{ $brand->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Danh sách sản phẩm --}}
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h4 mb-0 fw-bold">{{ $category->name }}</h1>
                <span class="text-muted small">{{ $products->total() }} sản phẩm</span>
            </div>

            @if($products->isEmpty())
                <div class="text-center py-5 text-muted">Chưa có sản phẩm nào trong danh mục này.</div>
            @else
                <div class="row g-3">
                    @foreach($products as $product)
                        @include('client.partials.product_card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-4">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
