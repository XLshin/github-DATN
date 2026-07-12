@extends('layouts.app')

@section('title', $product->name)

@php
    $productGroup = $product->productGroup;
    $versions = $productGroup?->products ?? collect([$product]);
    $showVersionSelector = $versions->count() > 1
        && $versions->contains(fn ($version) => filled($version->storage));
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
    $galleryImageUrls = $imagePaths->map(fn ($imagePath) => Storage::url($imagePath))->values();

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
            'in_stock' => $stockCount > 0,
            'stock_text' => $stockCount > 0 ? 'Còn hàng' : 'Tạm hết hàng',
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
        height: 360px;
        background: #f8fafc;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }

    .product-gallery-main .carousel-inner,
    .product-gallery-main .carousel-item {
        height: 100%;
    }

    .product-gallery-main .carousel-item {
        text-align: center;
    }

    .product-gallery-main:not(.carousel) {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-gallery-main img {
        width: auto;
        height: auto;
        max-width: 78%;
        max-height: 330px;
        object-fit: contain;
        margin: 0 auto;
    }

    .modal .product-gallery-main img {
        max-width: 92%;
        max-height: calc(70vh - 32px);
    }

    .gallery-nav-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        background: rgba(15, 23, 42, .62);
        color: #fff;
        display: grid;
        place-items: center;
        z-index: 2;
    }

    .gallery-nav-button:hover {
        background: rgba(15, 23, 42, .82);
    }

    .gallery-nav-button.prev {
        left: 12px;
    }

    .gallery-nav-button.next {
        right: 12px;
    }

    .gallery-open-layer {
        position: absolute;
        inset: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
        z-index: 1;
    }

    .gallery-indicators {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 10px;
    }

    .gallery-indicator {
        width: 8px;
        height: 8px;
        border: 0;
        border-radius: 50%;
        background: #cbd5e1;
        padding: 0;
    }

    .gallery-indicator.active {
        width: 22px;
        border-radius: 99px;
        background: #0d6efd;
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

    .stock-state {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }

    .stock-state::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }

    .stock-state.in-stock {
        color: #15803d;
    }

    .stock-state.out-stock {
        color: #dc2626;
    }

    .purchase-actions {
        display: grid;
        grid-template-columns: 56px 1fr;
        gap: 10px;
    }

    .cart-icon-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 54px;
        font-size: 1.35rem;
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

        .product-gallery-main {
            height: 300px;
        }

        .product-gallery-main img {
            max-height: 270px;
            max-width: 86%;
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

                    <div id="productGalleryCarousel" class="carousel slide product-gallery-main mb-2" data-bs-interval="false">
                        <div class="carousel-inner">
                            @forelse($galleryImageUrls as $index => $imageUrl)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <div class="h-100 d-flex align-items-center justify-content-center">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" data-gallery-carousel-image>
                                    </div>
                                    <button type="button" class="gallery-open-layer" data-gallery-open aria-label="Xem ảnh lớn"></button>
                                </div>
                            @empty
                                <div class="carousel-item active">
                                    <div class="h-100 d-flex align-items-center justify-content-center">
                                        <img src="{{ $mainImageUrl }}" alt="{{ $product->name }}" data-gallery-carousel-image>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        @if($galleryImageUrls->count() > 1)
                            <button type="button" class="gallery-nav-button prev" data-gallery-prev aria-label="Ảnh trước">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="gallery-nav-button next" data-gallery-next aria-label="Ảnh sau">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        @endif
                    </div>

                    @if($galleryImageUrls->count() > 1)
                        <div class="gallery-indicators">
                            @foreach($galleryImageUrls as $index => $imageUrl)
                                <button
                                    type="button"
                                    class="gallery-indicator {{ $index === 0 ? 'active' : '' }}"
                                    data-gallery-dot
                                    aria-label="Chuyển đến ảnh {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif

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
                                <button class="btn btn-link btn-sm text-decoration-none" type="button" data-bs-toggle="modal" data-bs-target="#specificationsModal">
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
                        <div
                            class="small stock-state {{ ($selectedVariantData['in_stock'] ?? false) ? 'in-stock' : 'out-stock' }}"
                            id="selectedStock">
                            {{ $selectedVariantData['stock_text'] ?? 'Chưa có biến thể màu' }}
                        </div>
                    </div>

                    @if($showVersionSelector)
                        <div class="mb-4">
                            <div class="fw-semibold mb-2">Phiên bản</div>
                            <div class="choice-grid">
                                @foreach($versions as $version)
                                    <a
                                        href="{{ route('products.show', $version) }}"
                                        class="choice-card {{ $version->id === $product->id ? 'active' : '' }}">
                                        <div class="fw-semibold">{{ $version->storage }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

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
                            <input type="hidden" name="quantity" value="1">

                            <div id="purchaseActions" @class(['purchase-actions', 'd-none' => !($selectedVariantData['in_stock'] ?? false)])>
                                <button type="submit" class="btn btn-outline-primary cart-icon-button" title="Thêm vào giỏ" aria-label="Thêm vào giỏ">
                                    <i class="lni lni-cart"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-lg">
                                    Mua ngay
                                </button>
                            </div>

                            <button
                                type="button"
                                id="outOfStockButton"
                                @class(['btn btn-secondary btn-lg w-100', 'd-none' => ($selectedVariantData['in_stock'] ?? false)])
                                disabled>
                                Tạm hết hàng
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
                        @php
                            $relatedImage = collect([
                                $relatedProduct->thumbnail,
                                $relatedProduct->productGroup?->images?->first()?->image_path,
                                $relatedProduct->images?->first()?->image_path,
                                $relatedProduct->variants?->first()?->image_path,
                                $relatedProduct->variants?->first()?->images?->first()?->image_path,
                            ])->filter()->first();
                        @endphp
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('products.show', $relatedProduct) }}" class="product-surface d-block h-100 p-3 text-decoration-none text-dark">
                                <div class="ratio ratio-1x1 bg-light rounded mb-3">
                                    @if($relatedImage)
                                        <img src="{{ Storage::url($relatedImage) }}" alt="{{ $relatedProduct->name }}" class="w-100 h-100 object-fit-contain p-2">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                            <i class="lni lni-image" style="font-size:2rem"></i>
                                        </div>
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

@if($specifications->isNotEmpty())
    <div class="modal fade" id="specificationsModal" tabindex="-1" aria-labelledby="specificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="specificationsModalLabel">Thông số kỹ thuật</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
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
            </div>
        </div>
    </div>
@endif

@if($galleryImageUrls->isNotEmpty())
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="galleryModalLabel">{{ $product->name }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="product-gallery-main bg-white mx-auto" style="height: 70vh; max-height: 70vh;">
                        @if($galleryImageUrls->count() > 1)
                            <button type="button" class="gallery-nav-button prev" data-gallery-prev aria-label="Ảnh trước">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="gallery-nav-button next" data-gallery-next aria-label="Ảnh sau">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        @endif
                        <img id="modalProductImage" src="{{ $mainImageUrl }}" alt="{{ $product->name }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectedPrice = document.getElementById('selectedPrice');
    const selectedVariantId = document.getElementById('selectedVariantId');
    const selectedStock = document.getElementById('selectedStock');
    const purchaseActions = document.getElementById('purchaseActions');
    const outOfStockButton = document.getElementById('outOfStockButton');
    const variantPayload = @json($variantPayload);
    const galleryImages = @json($galleryImageUrls);
    const galleryCarouselElement = document.getElementById('productGalleryCarousel');
    const galleryCarousel = galleryCarouselElement && window.bootstrap
        ? new bootstrap.Carousel(galleryCarouselElement, { interval: false, touch: true })
        : null;
    const modalImage = document.getElementById('modalProductImage');
    const galleryModalElement = document.getElementById('galleryModal');
    const galleryModal = galleryModalElement && window.bootstrap ? new bootstrap.Modal(galleryModalElement) : null;
    let currentGalleryIndex = 0;

    function setGalleryImage(index) {
        if (!galleryImages.length) {
            return;
        }

        currentGalleryIndex = (index + galleryImages.length) % galleryImages.length;
        const imageUrl = galleryImages[currentGalleryIndex];

        if (galleryCarousel) {
            galleryCarousel.to(currentGalleryIndex);
        }

        if (modalImage) {
            modalImage.src = imageUrl;
        }

        document.querySelectorAll('[data-gallery-thumb]').forEach(function (item, itemIndex) {
            item.classList.toggle('active', itemIndex === currentGalleryIndex);
        });

        document.querySelectorAll('[data-gallery-dot]').forEach(function (item, itemIndex) {
            item.classList.toggle('active', itemIndex === currentGalleryIndex);
        });
    }

    if (galleryCarouselElement) {
        galleryCarouselElement.addEventListener('slid.bs.carousel', function (event) {
            currentGalleryIndex = event.to;

            document.querySelectorAll('[data-gallery-thumb]').forEach(function (item, itemIndex) {
                item.classList.toggle('active', itemIndex === currentGalleryIndex);
            });

            document.querySelectorAll('[data-gallery-dot]').forEach(function (item, itemIndex) {
                item.classList.toggle('active', itemIndex === currentGalleryIndex);
            });

            if (modalImage && galleryImages[currentGalleryIndex]) {
                modalImage.src = galleryImages[currentGalleryIndex];
            }
        });
    }

    document.querySelectorAll('[data-gallery-thumb]').forEach(function (button) {
        button.addEventListener('click', function () {
            const index = Array.from(document.querySelectorAll('[data-gallery-thumb]')).indexOf(button);
            setGalleryImage(index);
        });
    });

    document.querySelectorAll('[data-gallery-prev]').forEach(function (button) {
        button.addEventListener('click', function () {
            setGalleryImage(currentGalleryIndex - 1);
        });
    });

    document.querySelectorAll('[data-gallery-next]').forEach(function (button) {
        button.addEventListener('click', function () {
            setGalleryImage(currentGalleryIndex + 1);
        });
    });

    document.querySelectorAll('[data-gallery-dot]').forEach(function (button) {
        button.addEventListener('click', function () {
            const index = Array.from(document.querySelectorAll('[data-gallery-dot]')).indexOf(button);
            setGalleryImage(index);
        });
    });

    document.querySelectorAll('[data-gallery-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (galleryModal) {
                galleryModal.show();
            }
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

            if (button.dataset.imageUrl) {
                const imageIndex = galleryImages.indexOf(button.dataset.imageUrl);
                if (imageIndex >= 0) {
                    setGalleryImage(imageIndex);
                } else {
                    if (modalImage) {
                        modalImage.src = button.dataset.imageUrl;
                    }
                }
            }

            if (variant) {
                if (selectedStock) {
                    selectedStock.textContent = variant.stock_text;
                    selectedStock.classList.toggle('in-stock', Boolean(variant.in_stock));
                    selectedStock.classList.toggle('out-stock', !variant.in_stock);
                }

                if (purchaseActions) {
                    purchaseActions.classList.toggle('d-none', !variant.in_stock);
                }

                if (outOfStockButton) {
                    outOfStockButton.classList.toggle('d-none', Boolean(variant.in_stock));
                }
            }
        });
    });
});
</script>
@endpush
