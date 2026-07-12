@extends('layouts.app')

@section('title', 'Sản phẩm')

@section('content')
<div class="container py-4">
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
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <p class="mb-0 text-muted small">
                    @if($products->total())
                        Hiển thị <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong> / <strong>{{ $products->total() }}</strong> sản phẩm
                    @else
                        Không tìm thấy sản phẩm
                    @endif
                </p>
                <select class="form-select form-select-sm" style="width:auto"
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
@endsection
