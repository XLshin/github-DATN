@extends('layouts.app')

@section('title', $product->name)

@section('header')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>
    <h1 class="h2 mb-0">{{ $product->name }}</h1>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted">{{ $product->description }}</p>
                    <div class="price-tag fs-4">{{ number_format($product->price, 0, ',', '.') }} đ</div>
                    @auth
                        <form method="POST" action="{{ route('cart.add') }}" class="d-flex gap-2 mt-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width:100px">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-cart-plus"></i> Thêm vào giỏ</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary mt-3">Đăng nhập để mua hàng</a>
                    @endauth
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><strong>Đánh giá sản phẩm</strong></div>
                <div class="card-body">
                    @forelse ($product->reviews as $review)
                        <div class="border-bottom pb-3 mb-3">
                            <strong>{{ $review->user->name ?? 'Khách' }}</strong>
                            <div class="text-warning small">{{ str_repeat('★', $review->rating) }}</div>
                            <p class="mb-0 small">{{ $review->comment }}</p>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Chưa có đánh giá.</p>
                    @endforelse

                    @auth
                        <form action="{{ route('reviews.store', $product) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Điểm</label>
                                <select name="rating" class="form-select" required>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ $i }} sao</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nhận xét</label>
                                <textarea name="comment" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm">Gửi đánh giá</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
