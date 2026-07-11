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
                    <div class="price-tag fs-4" id="product-price">{{ number_format($product->price, 0, ',', '.') }} đ</div>

                    @auth
                        <form method="POST" action="{{ route('cart.add') }}" class="add-to-cart-form mt-3" id="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" id="selected-variant-id" value="{{ $product->variants->first()->id ?? '' }}">

                            @if($product->variants->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label d-block">Màu sắc</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($product->variants as $variant)
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm variant-option {{ $loop->first ? 'active' : '' }}"
                                                data-variant-id="{{ $variant->id }}"
                                                data-extra-price="{{ (float) $variant->additional_price }}">
                                                {{ $variant->color }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width:100px">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-cart-plus"></i> Thêm vào giỏ</button>
                                <button type="button" class="btn btn-outline-primary" id="buy-now-btn">Mua ngay</button>
                            </div>
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

    @auth
    @push('scripts')
    <script>
    (function () {
        const basePrice = {{ (float) $product->price }};
        const priceEl = document.getElementById('product-price');
        const variantInput = document.getElementById('selected-variant-id');
        const buttons = document.querySelectorAll('.variant-option');

        function formatVND(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num)) + ' đ';
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                variantInput.value = btn.dataset.variantId;
                priceEl.textContent = formatVND(basePrice + parseFloat(btn.dataset.extraPrice || 0));
            });
        });

        const buyNowBtn = document.getElementById('buy-now-btn');
        if (buyNowBtn) {
            buyNowBtn.addEventListener('click', function () {
                const form = document.getElementById('add-to-cart-form');
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const token = tokenMeta ? tokenMeta.getAttribute('content') : null;

                buyNowBtn.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '{{ route('checkout.show') }}?items[]=' + data.item.id;
                        }
                    })
                    .finally(() => { buyNowBtn.disabled = false; });
            });
        }
    })();
    </script>
    @endpush
    @endauth
@endsection
