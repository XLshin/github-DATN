@extends('layouts.app')

@section('title', 'Trang chủ')

@section('header')
    <h1 class="h2 mb-1">Điện thoại chính hãng</h1>
    <p class="text-muted mb-0">Khám phá sản phẩm mới nhất tại Byte Zone Store</p>
@endsection

@section('content')
    @if ($products->isEmpty())
        <div class="alert alert-info">Chưa có sản phẩm nào.</div>
    @else
        <div class="row g-4">
            @foreach ($products as $product)
                <div class="col-md-6 col-lg-4">
                    <article class="product-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 mb-2">
                                <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                            </h2>
                            <p class="text-muted small flex-grow-1">{{ Str::limit($product->description, 100) }}</p>
                            <div class="price-tag mb-3">{{ number_format($product->price, 0, ',', '.') }} đ</div>

                            @auth
                                <form method="POST" action="{{ route('cart.add') }}" class="d-flex gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width:70px">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="bi bi-cart-plus"></i> Thêm giỏ
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Đăng nhập để mua</a>
                            @endauth
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
@endsection
