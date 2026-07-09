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

            @forelse($products as $product)
            @php
                $thumb   = $product->thumbnail
                    ? Storage::url($product->thumbnail)
                    : ($product->images->first() ? Storage::url($product->images->first()->image_path) : null);
                $maxPrice = $product->variants->max('additional_price');
                $inStock  = $product->variants->sum('stock_quantity') > 0;
            @endphp
            <div class="card border-0 shadow-sm mb-3">
                <div class="row g-0">
                    <div class="col-4 col-md-3">
                        <a href="{{ route('products.show', $product) }}">
                            @if($thumb)
                            <img src="{{ $thumb }}" alt="{{ $product->name }}"
                                class="img-fluid rounded-start w-100" style="height:160px; object-fit:cover;">
                            @else
                            <div class="d-flex align-items-center justify-content-center bg-light rounded-start" style="height:160px;">
                                <i class="lni lni-image text-muted fs-2"></i>
                            </div>
                            @endif
                        </a>
                    </div>
                    <div class="col-8 col-md-9">
                        <div class="card-body py-3 px-3 d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <h6 class="mb-0 fw-semibold">
                                    <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark">
                                        {{ $product->name }}
                                    </a>
                                </h6>
                                @if($inStock)
                                <span class="badge text-bg-success flex-shrink-0" style="font-size:10px;">Còn hàng</span>
                                @else
                                <span class="badge text-bg-secondary flex-shrink-0" style="font-size:10px;">Hết hàng</span>
                                @endif
                            </div>

                            <div class="text-muted small mb-2">
                                {{ $product->brand->name ?? '' }}
                                @if($product->category) · {{ $product->category->name }} @endif
                            </div>

                            <p class="small text-muted mb-2 flex-grow-1">
                                {{ \Illuminate\Support\Str::limit($product->description, 80) }}
                            </p>

                            @if($product->variants->count())
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($product->variants->take(4) as $v)
                                <span class="badge text-bg-light border text-dark" style="font-size:10px;">
                                    {{ $v->color }}{{ $v->storage ? ' · '.$v->storage : '' }}
                                </span>
                                @endforeach
                                @if($product->variants->count() > 4)
                                <span class="badge text-bg-light border text-muted" style="font-size:10px;">
                                    +{{ $product->variants->count() - 4 }}
                                </span>
                                @endif
                            </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="fw-semibold text-primary">
                                    @if($product->price > 0)
                                    {{ number_format($product->price, 0, ',', '.') }} đ
                                    @if($maxPrice > 0)
                                    <span class="text-muted small fw-normal">
                                        — {{ number_format($product->price + $maxPrice, 0, ',', '.') }} đ
                                    </span>
                                    @endif
                                    @else
                                    <span class="text-muted">Liên hệ</span>
                                    @endif
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="btn btn-primary btn-sm">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="lni lni-empty-file fs-1 d-block mb-3"></i>
                Không tìm thấy sản phẩm nào.
                <div class="mt-2">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Xóa bộ lọc</a>
                </div>
            </div>
            @endforelse

            @if($products->hasPages())
            <div class="mt-3">{{ $products->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
