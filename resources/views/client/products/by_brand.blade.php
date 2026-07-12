@extends('layouts.app')

@section('title', $brand->name . ' — Byte Zone Store')

@section('content')
<div class="container py-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active">{{ $brand->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Sidebar lọc --}}
        <div class="col-lg-3">
            {{-- Logo thương hiệu --}}
            @if($brand->logo)
            <div class="border rounded-3 p-3 text-center mb-3">
                <img src="{{ asset('storage/' . $brand->logo) }}"
                     alt="{{ $brand->name }}" style="max-height:60px;object-fit:contain">
            </div>
            @endif

            <div class="border rounded-3 p-3">
                <h6 class="fw-bold mb-3">Danh mục</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-1">
                        <a href="{{ route('brand.products', $brand) }}"
                           class="text-decoration-none {{ !request('category_id') ? 'fw-bold text-primary' : 'text-dark' }}">
                            Tất cả
                        </a>
                    </li>
                    @foreach($categories as $category)
                    <li class="mb-1">
                        <a href="{{ route('brand.products', $brand) }}?category_id={{ $category->id }}"
                           class="text-decoration-none {{ request('category_id') == $category->id ? 'fw-bold text-primary' : 'text-dark' }}">
                            {{ $category->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Danh sách sản phẩm --}}
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h4 mb-0 fw-bold">{{ $brand->name }}</h1>
                <span class="text-muted small">{{ $products->total() }} sản phẩm</span>
            </div>

            @if($products->isEmpty())
                <div class="text-center py-5 text-muted">Chưa có sản phẩm nào của thương hiệu này.</div>
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
