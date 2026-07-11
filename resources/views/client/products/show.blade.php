@extends('layouts.app')

@section('title', $product->name)

@php
    $productGroup = $product->productGroup;
    $versions = $productGroup?->products ?? collect([$product]);
    $specifications = $productGroup?->specifications ?? collect();
    $highlightSpecifications = $specifications->take(3);
    $groupedSpecifications = $specifications->groupBy(fn ($specification) => $specification->group_name ?: 'Thông số khác');
    $selectedVariant = $product->variants->first();

    $imagePaths = collect()
        ->merge($productGroup?->images?->pluck('image_path') ?? [])
        ->merge($product->images?->pluck('image_path') ?? [])
        ->merge($product->variants->pluck('image_path')->filter())
        ->merge($product->variants->flatMap(fn ($variant) => $variant->images?->pluck('image_path') ?? []))
        ->when($product->thumbnail, fn ($collection) => $collection->prepend($product->thumbnail))
        ->filter()
        ->unique()
        ->values();

    $mainImage = $imagePaths->first();
    $mainImageUrl = $mainImage ? Storage::url($mainImage) : asset('images/no-image.png');

    $variantPayload = $product->variants->map(function ($variant) use ($product, $mainImageUrl) {
        $imagePath = $variant->image_path ?: $variant->images->first()?->image_path;
        $stockCount = $product->product_type === 'imei/serial'
            ? (int) ($variant->available_imeis_count ?? 0)
            : (int) ($variant->stock_quantity ?? 0);

        return [
            'id' => $variant->id,
            'color' => $variant->color ?: 'Không màu',
            'additional_price' => (float) ($variant->additional_price ?? 0),
            'final_price' => (float) ($product->price ?? 0) + (float) ($variant->additional_price ?? 0),
            'image_url' => $imagePath ? Storage::url($imagePath) : $mainImageUrl,
            'stock_count' => $stockCount,
            'stock_text' => $stockCount > 0 ? 'Còn ' . $stockCount . ' sản phẩm' : 'Tạm hết hàng',
        ];
    })->values();

    $selectedVariantData = $variantPayload->first();
    $selectedFinalPrice = $selectedVariantData['final_price'] ?? (float) ($product->price ?? 0);
@endphp

@push('styles')
<style>
    .product-detail-shell {
        background: #f5f7fb;
        padding: 24px 0 40px;
    }

    .product-surface {
        background: #fff;
        border: 1px solid #e6ebf2;
        border-radius: 8px;
    }

    .product-gallery-main {
        aspect-ratio: 1 / 1;
        display: grid;
        place-items: center;
        background: #f8fafc;
        border-radius: 8px;
        overflow: hidden;
    }

    .product-gallery-main img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-thumb {
        width: 72px;
        height: 72px;
        border: 1px solid #dbe3ee;
        border-radius: 8px;
        background: #fff;
        padding: 4px;
        cursor: pointer;
    }

    .product-thumb.active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, .12);
    }

    .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .choice-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
        gap: 10px;
    }

    .choice-card {
        border: 1px solid #dbe3ee;
        border-radius: 8px;
        background: #fff;
        color: #1f2937;
        min-height: 52px;
        padding: 10px 12px;
        text-align: left;
        text-decoration: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .choice-card:hover,
    .choice-card.active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, .1);
        color: #0d6efd;
    }

    .color-choice {
        display: flex;
        gap: 10px;
        align-items: center;
        width: 100%;
    }

    .color-choice img {
        width: 36px;
        height: 36px;
        object-fit: contain;
        border-radius: 6px;
        background: #f8fafc;
        flex: 0 0 auto;
    }

    .spec-highlight {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 10px;
    }

    .spec-highlight-item {
        border: 1px solid #e6ebf2;
        border-radius: 8px;
        padding: 12px;
        background: #f8fafc;
        min-height: 82px;
    }

    .spec-table th {
        width: 34%;
        color: #64748b;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .buy-panel {
            position: static !important;
        }
    }
</style>
@endpush

@section('header')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
        @if($product->category)
            <li class="breadcrumb-item">
                <a href="{{ route('category.products', $product->category) }}">{{ $product->category->name }}</a>
            </li>
        @endif
        <li class="breadcrumb-item active">{{ $product->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="product-detail-shell">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <section class="product-surface p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h1 class="h3 mb-2">{{ $product->name }}</h1>
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                @if($product->brand)
                                    <span><i class="bi bi-award"></i> {{ $product->brand->name }}</span>
                                @endif
                                <span><i class="bi bi-hdd"></i> {{ $product->storage ?? 'Không có dung lượng' }}</span>
                                <span><i class="bi bi-star-fill text-warning"></i> {{ $product->reviews->count() }} đánh giá</span>
                            </div>
                        </div>
                        <span class="badge text-bg-{{ $product->status ? 'success' : 'secondary' }}">
                            {{ $product->status ? 'Đang bán' : 'Ngừng bán' }}
                        </span>
                    </div>

                    <div class="product-gallery-main mb-3">
                        <img id="mainProductImage" src="{{ $mainImageUrl }}" alt="{{ $product->name }}">
                    </div>

                    @if($imagePaths->isNotEmpty())
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($imagePaths as $index => $imagePath)
                                <button
                                    type="button"
                                    class="product-thumb {{ $index === 0 ? 'active' : '' }}"
                                    data-gallery-thumb
                                    data-image-url="{{ Storage::url($imagePath) }}"
                                    aria-label="Ảnh sản phẩm {{ $index + 1 }}">
                                    <img src="{{ Storage::url($imagePath) }}" alt="{{ $product->name }}">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($highlightSpecifications->isNotEmpty())
                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h2 class="h5 mb-0">Thông số nổi bật</h2>
                                <button class="btn btn-link btn-sm text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#fullSpecifications">
                                    Xem tất cả
                                </button>
                            </div>
                            <div class="spec-highlight">
                                @foreach($highlightSpecifications as $specification)
                                    <div class="spec-highlight-item">
                                        <div class="text-muted small">{{ $specification->name }}</div>
                                        <div class="fw-semibold mt-1">{{ $specification->value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <div class="col-lg-5">
                <aside class="product-surface p-3 p-md-4 buy-panel position-sticky" style="top: 16px;">
                    <div class="mb-3">
                        <div class="text-muted small">Giá bán</div>
                        <div class="display-6 fw-bold text-danger" id="selectedPrice">
                            {{ number_format($selectedFinalPrice, 0, ',', '.') }} đ
                        </div>
                        <div class="text-muted small" id="selectedStock">
                            {{ $selectedVariantData['stock_text'] ?? 'Chưa có biến thể màu' }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="fw-semibold mb-2">Phiên bản</div>
                        <div class="choice-grid">
                            @foreach($versions as $version)
                                <a
                                    href="{{ route('products.show', $version) }}"
                                    class="choice-card {{ $version->id === $product->id ? 'active' : '' }}">
                                    <div class="fw-semibold">{{ $version->storage ?: $version->name }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="fw-semibold mb-2">Màu sắc</div>
                        @if($product->variants->isNotEmpty())
                            <div class="choice-grid">
                                @foreach($product->variants as $variant)
                                    @php
                                        $variantImage = $variant->image_path ?: $variant->images->first()?->image_path;
                                        $variantImageUrl = $variantImage ? Storage::url($variantImage) : $mainImageUrl;
                                        $variantFinalPrice = (float) $product->price + (float) ($variant->additional_price ?? 0);
                                    @endphp
                                    <button
                                        type="button"
                                        class="choice-card color-choice {{ $selectedVariant?->id === $variant->id ? 'active' : '' }}"
                                        data-variant-option
                                        data-variant-id="{{ $variant->id }}"
                                        data-image-url="{{ $variantImageUrl }}"
                                        data-price="{{ number_format($variantFinalPrice, 0, ',', '.') }} đ">
                                        <img src="{{ $variantImageUrl }}" alt="{{ $variant->color ?: 'Không màu' }}">
                                        <span>
                                            <span class="d-block fw-semibold">{{ $variant->color ?: 'Không màu' }}</span>
                                            <span class="small text-muted">{{ number_format($variantFinalPrice, 0, ',', '.') }} đ</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">Sản phẩm này chưa có biến thể màu.</div>
                        @endif
                    </div>

                    @auth
                        <form method="POST" action="{{ route('cart.add') }}" class="d-grid gap-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" id="selectedVariantId" value="{{ $selectedVariant?->id }}">

                            <div class="input-group">
                                <span class="input-group-text">Số lượng</span>
                                <input type="number" name="quantity" value="1" min="1" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg" @disabled(!$selectedVariant)>
                                <i class="bi bi-cart-plus"></i>
                                Thêm vào giỏ
                            </button>
                            <button type="submit" name="intent" value="buy_now" class="btn btn-danger btn-lg" @disabled(!$selectedVariant)>
                                Mua ngay
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
                            Đăng nhập để mua hàng
                        </a>
                    @endauth
                </aside>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-7">
                <section class="product-surface p-3 p-md-4">
                    <h2 class="h5 mb-3">Mô tả sản phẩm</h2>
                    <div class="text-muted">
                        {!! nl2br(e($product->description ?: $productGroup?->description ?: 'Chưa có mô tả cho sản phẩm này.')) !!}
                    </div>
                </section>

                <section class="product-surface p-3 p-md-4 mt-4">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="h5 mb-0">Thông số kỹ thuật</h2>
                        @if($specifications->isNotEmpty())
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#fullSpecifications">
                                Xem / ẩn thông số
                            </button>
                        @endif
                    </div>

                    @if($specifications->isEmpty())
                        <p class="text-muted mb-0">Chưa có thông số kỹ thuật.</p>
                    @else
                        <div class="collapse show" id="fullSpecifications">
                            @foreach($groupedSpecifications as $groupName => $items)
                                <div class="mb-4">
                                    <h3 class="h6 mb-2">{{ $groupName }}</h3>
                                    <div class="table-responsive">
                                        <table class="table table-bordered spec-table mb-0">
                                            <tbody>
                                                @foreach($items as $specification)
                                                    <tr>
                                                        <th>{{ $specification->name }}</th>
                                                        <td>{{ $specification->value }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <div class="col-lg-5">
                <section class="product-surface p-3 p-md-4">
                    <h2 class="h5 mb-3">Đánh giá sản phẩm</h2>

                    @forelse($product->reviews as $review)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <strong>{{ $review->user->name ?? 'Khách' }}</strong>
                                <span class="text-warning small">{{ str_repeat('★', (int) $review->rating) }}</span>
                            </div>
                            <p class="mb-0 small text-muted">{{ $review->comment }}</p>
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
                                    @for($i = 5; $i >= 1; $i--)
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
                </section>
            </div>
        </div>

        @if($relatedProducts->isNotEmpty())
            <section class="mt-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 mb-0">Sản phẩm cùng thương hiệu</h2>
                    @if($product->brand)
                        <a href="{{ route('brand.products', $product->brand) }}" class="btn btn-link btn-sm text-decoration-none">
                            Xem thêm
                        </a>
                    @endif
                </div>

                <div class="row g-3">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('products.show', $relatedProduct) }}" class="product-surface d-block h-100 p-3 text-decoration-none text-dark">
                                <div class="ratio ratio-1x1 bg-light rounded mb-3">
                                    @if($relatedProduct->thumbnail)
                                        <img src="{{ Storage::url($relatedProduct->thumbnail) }}" alt="{{ $relatedProduct->name }}" class="w-100 h-100 object-fit-contain p-2">
                                    @endif
                                </div>
                                <div class="fw-semibold">{{ $relatedProduct->name }}</div>
                                <div class="text-danger fw-bold mt-1">{{ number_format((float) $relatedProduct->price, 0, ',', '.') }} đ</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainImage = document.getElementById('mainProductImage');
    const selectedPrice = document.getElementById('selectedPrice');
    const selectedVariantId = document.getElementById('selectedVariantId');
    const variantPayload = @json($variantPayload);

    document.querySelectorAll('[data-gallery-thumb]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (mainImage) {
                mainImage.src = button.dataset.imageUrl;
            }

            document.querySelectorAll('[data-gallery-thumb]').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
        });
    });

    document.querySelectorAll('[data-variant-option]').forEach(function (button) {
        button.addEventListener('click', function () {
            const variantId = Number(button.dataset.variantId);
            const variant = variantPayload.find((item) => Number(item.id) === variantId);

            document.querySelectorAll('[data-variant-option]').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            if (selectedVariantId) {
                selectedVariantId.value = button.dataset.variantId;
            }

            if (selectedPrice && button.dataset.price) {
                selectedPrice.textContent = button.dataset.price;
            }

            if (mainImage && button.dataset.imageUrl) {
                mainImage.src = button.dataset.imageUrl;
            }

            if (variant) {
                const stockNode = document.getElementById('selectedStock');
                if (stockNode) {
                    stockNode.textContent = variant.stock_text;
                }
            }
        });
    });
});
</script>
@endpush
