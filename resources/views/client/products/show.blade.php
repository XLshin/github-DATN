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
    @php
        $defaultPrice = $defaultVariant['price'] ?? (float) $product->price;
        $defaultStock = $defaultVariant['stock_quantity'] ?? (int) $product->stock_quantity;
        $defaultAvailable = $defaultVariant['is_available'] ?? $defaultStock > 0;
    @endphp

    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="ratio ratio-1x1 mb-3">
                            @if($productImages->isNotEmpty())
                                <img id="mainProductImage" src="{{ $productImages->first() }}" alt="{{ $product->name }}"
                                    class="w-100 h-100 rounded" style="object-fit: cover;">
                            @else
                                <div class="w-100 h-100 rounded bg-light d-flex align-items-center justify-content-center text-muted">
                                    <i class="lni lni-image fs-1"></i>
                                </div>
                            @endif
                        </div>

                        @if($productImages->count() > 1)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($productImages as $image)
                                    <button type="button" class="btn btn-outline-secondary p-1" data-image-url="{{ $image }}" onclick="setMainImage(this.dataset.imageUrl)">
                                        <img src="{{ $image }}" alt="thumb" class="rounded" style="width:72px;height:72px;object-fit:cover;">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted small mb-2">
                            {{ $product->brand?->name }} @if($product->category) · {{ $product->category->name }} @endif
                        </div>
                        <h2 class="h3 mb-3">{{ $product->name }}</h2>

                        <div class="mb-3">
                            <div class="text-primary fw-bold fs-3" id="priceText">{{ number_format($defaultPrice, 0, ',', '.') }} đ</div>
                            @if($versionOptions->isNotEmpty())
                                <div class="text-muted small" id="variantHint">Phiên bản hiện tại: <strong>{{ $defaultStorage }}</strong></div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <span class="badge rounded-pill px-3 py-2" id="stockBadge" style="background:#e8f7ed;color:#1f8f4d;">
                                {{ $defaultAvailable ? 'Còn hàng' : 'Hết hàng' }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <p class="text-muted mb-0">{{ $product->description }}</p>
                        </div>

                        <div class="mb-3">
                            @if($versionOptions->isNotEmpty())
                                <label class="form-label fw-semibold">Phiên bản</label>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach($versionOptions as $version)
                                        <button type="button"
                                            class="btn btn-outline-secondary text-start version-option {{ $defaultStorage === $version ? 'btn-primary text-white' : '' }}"
                                            data-version="{{ $version }}"
                                            onclick="selectVersion(this)">
                                            <div class="fw-semibold">{{ $version }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <label class="form-label fw-semibold">Màu sắc</label>
                            <div id="colorOptionsContainer" class="d-flex flex-wrap gap-2">
                                @if($initialColorOptions->isNotEmpty())
                                    @foreach($initialColorOptions as $colorOption)
                                        <button type="button"
                                            class="btn btn-outline-secondary text-start color-option {{ $loop->first ? 'btn-primary text-white' : '' }}"
                                            data-color-name="{{ $colorOption['name'] }}"
                                            data-variant-id="{{ $colorOption['id'] }}"
                                            data-price="{{ $colorOption['price'] }}"
                                            data-stock="{{ $colorOption['stock_quantity'] }}"
                                            data-available="{{ $colorOption['is_available'] ? '1' : '0' }}"
                                            data-image-url="{{ $colorOption['image_url'] ?? '' }}"
                                            onclick="selectColor(this)">
                                            <div class="fw-semibold">{{ $colorOption['name'] }}</div>
                                            <div class="small">
                                                @if((float) $colorOption['additional_price'] > 0)
                                                    +{{ number_format($colorOption['additional_price'], 0, ',', '.') }} đ
                                                @else
                                                    Giá gốc
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        @auth
                            <form method="POST" action="{{ route('cart.add') }}" class="d-flex flex-wrap gap-2 mt-4">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="variant_id" id="selectedVariantId" value="{{ $defaultVariant['id'] ?? '' }}">
                                <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width:120px;">
                                <button type="submit" class="btn btn-primary" id="addToCartBtn" @disabled(!$defaultAvailable)>
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </button>
                                <button type="submit" formaction="{{ route('buy.now') }}" class="btn btn-outline-primary" id="buyNowBtn" @disabled(!$defaultAvailable)>
                                    Mua ngay
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary mt-4">Đăng nhập để mua hàng</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><strong>Thông số kỹ thuật</strong></div>
                    <div class="card-body">
                        @if($specifications->isNotEmpty())
                            @php $groupedSpecs = $specifications->groupBy(fn ($item) => $item->group_name ?: 'Thông tin chung'); @endphp
                            @foreach($groupedSpecs as $groupName => $items)
                                <h6 class="fw-semibold mt-3">{{ $groupName }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <tbody>
                                            @foreach($items as $item)
                                                <tr>
                                                    <td class="fw-semibold" style="width:35%;">{{ $item->name }}</td>
                                                    <td>{{ $item->value }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">Chưa có thông số kỹ thuật cho sản phẩm này.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white"><strong>Đánh giá sản phẩm</strong></div>
                    <div class="card-body">
                        @forelse ($product->reviews as $review)
                            <div class="border-bottom pb-3 mb-3">
                                <strong>{{ $review->user->name ?? 'Khách' }}</strong>
                                <div class="text-warning small">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                                <p class="mb-0 small text-muted">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Chưa có đánh giá.</p>
                        @endforelse

                        @auth
                            <form action="{{ route('reviews.store', $product) }}" method="POST" class="mt-3 pt-3 border-top">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Điểm đánh giá</label>
                                    <select name="rating" class="form-select form-select-sm" required>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}">{{ $i }} ★</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Nhận xét</label>
                                    <textarea name="comment" class="form-control form-control-sm" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm">Gửi đánh giá</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const selectedVariantIdInput = document.getElementById('selectedVariantId');
    const priceText = document.getElementById('priceText');
    const stockBadge = document.getElementById('stockBadge');
    const stockText = document.getElementById('stockQuantity');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const buyNowBtn = document.getElementById('buyNowBtn');
    const mainProductImage = document.getElementById('mainProductImage');
    const versionHint = document.getElementById('variantHint');
    const colorOptionsContainer = document.getElementById('colorOptionsContainer');
    const versionButtons = Array.from(document.querySelectorAll('.version-option'));
    const variantData = @json($variantData);

    let selectedVersion = @json($defaultStorage);
    let selectedColor = '';

    function setMainImage(url) {
        if (!mainProductImage) return;
        mainProductImage.src = url;
    }

    function getColorOptionsForVersion(version) {
        const matching = variantData.filter((variant) => variant.storage === version);
        const grouped = matching.reduce((acc, variant) => {
            if (!acc[variant.color]) {
                acc[variant.color] = variant;
            }
            return acc;
        }, {});

        return Object.entries(grouped).map(([name, variant]) => ({
            name,
            id: variant.id,
            price: variant.price,
            stock_quantity: variant.stock_quantity,
            is_available: variant.is_available,
            image_url: variant.image_url,
            additional_price: variant.additional_price,
        }));
    }

    function renderColorOptions(version) {
        const colorOptions = getColorOptionsForVersion(version);
        if (!colorOptionsContainer) return;

        if (!selectedColor || !colorOptions.some((option) => option.name === selectedColor)) {
            selectedColor = colorOptions[0]?.name || '';
        }

        colorOptionsContainer.innerHTML = colorOptions.map((option) => `
            <button type="button"
                class="btn btn-outline-secondary text-start color-option ${selectedColor === option.name ? 'btn-primary text-white' : ''}"
                data-color-name="${option.name}"
                data-variant-id="${option.id}"
                data-price="${option.price}"
                data-stock="${option.stock_quantity}"
                data-available="${option.is_available ? '1' : '0'}"
                data-image-url="${option.image_url || ''}">
                <div class="fw-semibold">${option.name}</div>
                <div class="small">
                    ${Number(option.additional_price) > 0 ? `+${Number(option.additional_price).toLocaleString('vi-VN')} đ` : 'Giá gốc'}
                </div>
            </button>
        `).join('');

        document.querySelectorAll('.color-option').forEach((button) => {
            button.addEventListener('click', () => selectColor(button));
        });

        updateVariantUiFromSelection();
    }

    function updateVariantUiFromSelection() {
        const selectedVariant = variantData.find((variant) => variant.storage === selectedVersion && variant.color === selectedColor)
            || variantData.find((variant) => variant.storage === selectedVersion)
            || variantData[0];

        if (!selectedVariant) return;

        if (selectedVariantIdInput) {
            selectedVariantIdInput.value = selectedVariant.id;
        }

        if (priceText) {
            priceText.textContent = Number(selectedVariant.price).toLocaleString('vi-VN') + ' đ';
        }

        if (stockText) {
            stockText.textContent = selectedVariant.stock_quantity;
        }

        if (stockBadge) {
            if (selectedVariant.is_available) {
                stockBadge.textContent = 'Còn hàng';
                stockBadge.style.background = '#e8f7ed';
                stockBadge.style.color = '#1f8f4d';
            } else {
                stockBadge.textContent = 'Hết hàng';
                stockBadge.style.background = '#fef2f2';
                stockBadge.style.color = '#dc2626';
            }
        }

        if (addToCartBtn) {
            addToCartBtn.disabled = !selectedVariant.is_available;
        }

        if (buyNowBtn) {
            buyNowBtn.disabled = !selectedVariant.is_available;
        }

        if (versionHint) {
            versionHint.innerHTML = `Phiên bản hiện tại: <strong>${selectedVersion}</strong>`;
        }

        if (selectedVariant.image_url) {
            setMainImage(selectedVariant.image_url);
        }
    }

    function selectVersion(button) {
        if (!button) return;

        selectedVersion = button.dataset.version;
        versionButtons.forEach((item) => {
            item.classList.remove('btn-primary', 'text-white');
            item.classList.add('btn-outline-secondary');
        });

        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-primary', 'text-white');

        renderColorOptions(selectedVersion);
    }

    function selectColor(button) {
        if (!button) return;

        selectedColor = button.dataset.colorName;
        document.querySelectorAll('.color-option').forEach((item) => {
            item.classList.remove('btn-primary', 'text-white');
            item.classList.add('btn-outline-secondary');
        });

        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-primary', 'text-white');

        updateVariantUiFromSelection();
    }

    if (versionButtons.length) {
        selectVersion(versionButtons.find((button) => button.classList.contains('btn-primary')) || versionButtons[0]);
    }
</script>
@endpush
