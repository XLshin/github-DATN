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
    $isImei    = $product->product_type === 'imei/serial';
    $variants  = $product->variants;
    $colors    = $variants->pluck('color')->unique()->filter()->values();
    $storages  = $variants->pluck('storage')->unique()->filter()->values();
@endphp

<div class="row g-4" id="product-detail">

    {{-- ===== CỘT TRÁI: Thông tin + Mua hàng ===== --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                {{-- Ảnh sản phẩm --}}
                @if($product->thumbnail)
                <div class="text-center mb-4">
                    <img id="product-img"
                         src="{{ asset('storage/' . $product->thumbnail) }}"
                         alt="{{ $product->name }}"
                         class="rounded border"
                         style="max-height:260px;max-width:100%;object-fit:contain">
                </div>
                @endif

                {{-- Mô tả --}}
                <p class="text-muted mb-3">{{ $product->description }}</p>

                {{-- Giá --}}
                <div class="fs-3 fw-bold text-primary mb-4" id="product-price">
                    {{ number_format($product->price, 0, ',', '.') }} đ
                </div>

                {{-- ===== Chọn biến thể ===== --}}
                @if($variants->isNotEmpty())
                <div id="variant-section" class="mb-4">

                    @if($colors->isNotEmpty())
                    <div class="mb-3">
                        <div class="text-muted small fw-semibold mb-2">Màu sắc:</div>
                        <div class="d-flex flex-wrap gap-2" id="color-options">
                            @foreach($colors as $color)
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm variant-btn"
                                    data-type="color" data-value="{{ $color }}">
                                {{ $color }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($storages->isNotEmpty())
                    <div class="mb-3">
                        <div class="text-muted small fw-semibold mb-2">Dung lượng / Phiên bản:</div>
                        <div class="d-flex flex-wrap gap-2" id="storage-options">
                            @foreach($storages as $storage)
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm variant-btn"
                                    data-type="storage" data-value="{{ $storage }}">
                                {{ $storage }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Tồn kho --}}
                    <div id="stock-info" class="d-none mt-2">
                        <span class="badge" id="stock-badge">
                            <i class="bi bi-box-seam me-1"></i>
                            <span id="stock-text">...</span>
                        </span>
                    </div>
                </div>
                @endif

                {{-- ===== Form thêm giỏ ===== --}}
                @auth
                @if(auth()->user()->role === 'customer')

                <form id="product-add-form"
                      data-url="{{ route('cart.add') }}"
                      data-product-id="{{ $product->id }}">
                    @csrf
                    <input type="hidden" id="selected-variant-id" name="variant_id" value="">

                    <div class="d-flex gap-3 align-items-center flex-wrap">
                        {{-- Nút +/- --}}
                        <div class="input-group" style="width:136px">
                            <button type="button" class="btn btn-outline-secondary fw-bold fs-5 lh-1"
                                    id="qty-minus" style="width:40px" title="Giảm">−</button>
                            <input type="number" id="qty-input" name="quantity"
                                   value="1" min="1" max="99"
                                   class="form-control text-center fw-semibold"
                                   style="width:56px">
                            <button type="button" class="btn btn-outline-secondary fw-bold fs-5 lh-1"
                                    id="qty-plus" style="width:40px" title="Tăng">+</button>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 flex-grow-1" id="add-btn"
                                {{ $variants->isNotEmpty() ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-1"></i>Thêm vào giỏ
                        </button>
                    </div>

                    @if($variants->isNotEmpty())
                    <div class="text-muted small mt-2" id="select-hint">
                        <i class="bi bi-arrow-up-circle me-1"></i>Vui lòng chọn phiên bản trước
                    </div>
                    @endif
                </form>

                @elseif(in_array(auth()->user()->role, ['admin','staff'], true))
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-speedometer2 me-1"></i>Vào trang quản trị
                </a>
                @endif
                @else
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="bi bi-person me-1"></i>Đăng nhập để mua hàng
                </a>
                @endauth

            </div>
        </div>
    </div>

    {{-- ===== CỘT PHẢI: Đánh giá ===== --}}
    <div class="col-lg-5">
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

</div>{{-- /row --}}
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ---- Dữ liệu biến thể từ PHP ---- */
    const variants = @json($variants->map(fn($v) => [
        'id'      => $v->id,
        'color'   => $v->color,
        'storage' => $v->storage,
        'stock'   => $v->available_stock,
        'image'   => $v->image_path,
    ]));

    const isImei      = {{ $isImei ? 'true' : 'false' }};
    const basePrice   = {{ (int) $product->price }};
    const baseStorage = '{{ asset('storage/') }}/';

    let selectedColor   = null;
    let selectedStorage = null;

    /* ---- Elements ---- */
    const addBtn      = document.getElementById('add-btn');
    const variantInput= document.getElementById('selected-variant-id');
    const qtyInput    = document.getElementById('qty-input');
    const qtyMinus    = document.getElementById('qty-minus');
    const qtyPlus     = document.getElementById('qty-plus');
    const stockInfo   = document.getElementById('stock-info');
    const stockText   = document.getElementById('stock-text');
    const stockBadge  = document.getElementById('stock-badge');
    const selectHint  = document.getElementById('select-hint');
    const productImg  = document.getElementById('product-img');

    /* ---- Tìm variant khớp với lựa chọn ---- */
    function findVariant() {
        const needColor   = document.getElementById('color-options')   !== null;
        const needStorage = document.getElementById('storage-options') !== null;
        return variants.find(v => {
            const colorOk   = !needColor   || v.color   === selectedColor;
            const storageOk = !needStorage || v.storage === selectedStorage;
            return colorOk && storageOk;
        }) ?? null;
    }

    /* ---- Cập nhật UI sau khi chọn variant ---- */
    function updateVariantUI() {
        const v = findVariant();
        if (!v) {
            // Chưa đủ lựa chọn
            if (stockInfo) stockInfo.classList.add('d-none');
            if (addBtn) { addBtn.disabled = true; }
            if (selectHint) selectHint.classList.remove('d-none');
            return;
        }

        /* Variant đã chọn đủ */
        variantInput.value = v.id;

        /* Cập nhật ảnh nếu có */
        if (productImg && v.image) {
            productImg.src = '{{ asset('storage/') }}/' + v.image;
        }

        /* Cập nhật tồn kho */
        const stock = v.stock ?? 0;
        if (stockInfo) stockInfo.classList.remove('d-none');

        if (stock <= 0) {
            stockText.textContent = 'Hết hàng';
            stockBadge.className  = 'badge bg-danger';
            addBtn.disabled       = true;
            if (selectHint) selectHint.classList.add('d-none');
        } else {
            const maxQty = isImei ? 1 : stock;
            stockText.textContent = isImei
                ? `Còn ${stock} máy`
                : `Còn ${stock} sản phẩm`;
            stockBadge.className  = stock <= 5 ? 'badge bg-warning text-dark' : 'badge bg-success';
            addBtn.disabled       = false;
            if (selectHint) selectHint.classList.add('d-none');

            /* Giới hạn qty --*/
            qtyInput.max = maxQty;
            if (isImei) {
                qtyInput.value = 1;
                qtyInput.readOnly = true;
                if (qtyMinus) qtyMinus.disabled = true;
                if (qtyPlus)  qtyPlus.disabled  = true;
            } else {
                qtyInput.readOnly = false;
                if (parseInt(qtyInput.value) > maxQty) qtyInput.value = maxQty;
                updatePlusMinusState();
            }
        }
    }

    /* ---- Nếu không có variants (sản phẩm đơn giản) ---- */
    if (variants.length === 0 && addBtn) {
        addBtn.disabled = false;
        /* Không giới hạn stock (hoặc dùng product.stock_quantity nếu muốn) */
    }

    /* ---- +/- buttons ---- */
    function updatePlusMinusState() {
        if (!qtyMinus || !qtyPlus) return;
        const val = parseInt(qtyInput.value) || 1;
        const max = parseInt(qtyInput.max)   || 99;
        qtyMinus.disabled = val <= 1;
        qtyPlus.disabled  = val >= max;
    }

    if (qtyMinus) {
        qtyMinus.addEventListener('click', () => {
            let v = Math.max(1, (parseInt(qtyInput.value) || 1) - 1);
            qtyInput.value = v;
            updatePlusMinusState();
        });
    }
    if (qtyPlus) {
        qtyPlus.addEventListener('click', () => {
            const max = parseInt(qtyInput.max) || 99;
            let v = Math.min(max, (parseInt(qtyInput.value) || 1) + 1);
            qtyInput.value = v;
            updatePlusMinusState();
        });
    }
    if (qtyInput) {
        qtyInput.addEventListener('input', () => {
            let v = parseInt(qtyInput.value);
            const max = parseInt(qtyInput.max) || 99;
            if (isNaN(v) || v < 1) v = 1;
            if (v > max) v = max;
            qtyInput.value = v;
            updatePlusMinusState();
        });
    }
    updatePlusMinusState();

    /* ---- Chọn variant buttons ---- */
    document.querySelectorAll('.variant-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type  = btn.dataset.type;
            const value = btn.dataset.value;

            /* Highlight trong group */
            document.querySelectorAll(`.variant-btn[data-type="${type}"]`).forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary', 'active');

            if (type === 'color')   selectedColor   = value;
            if (type === 'storage') selectedStorage = value;

            updateVariantUI();
        });
    });

    /* ---- Auto-select nếu chỉ có 1 variant ---- */
    if (variants.length === 1) {
        const v = variants[0];
        if (v.color) {
            const btn = document.querySelector(`.variant-btn[data-type="color"][data-value="${v.color}"]`);
            btn?.click();
        }
        if (v.storage) {
            const btn = document.querySelector(`.variant-btn[data-type="storage"][data-value="${v.storage}"]`);
            btn?.click();
        }
    }

    /* ---- Toast notification ---- */
    let toastEl = document.getElementById('ajax-toast-container');
    if (!toastEl) {
        toastEl = document.createElement('div');
        toastEl.id = 'ajax-toast-container';
        toastEl.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none';
        document.body.appendChild(toastEl);
    }
    function showToast(msg, type) {
        const el  = document.createElement('div');
        const bg  = type === 'error' ? '#dc3545' : '#198754';
        const ico = type === 'error' ? '✕' : '✓';
        el.style.cssText = `background:${bg};color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;font-weight:500;
            box-shadow:0 4px 14px rgba(0,0,0,.25);display:flex;align-items:center;gap:10px;
            opacity:0;transform:translateY(16px);transition:opacity .25s,transform .25s;pointer-events:auto;min-width:220px`;
        el.innerHTML = `<span style="font-size:18px;line-height:1">${ico}</span>${msg}`;
        toastEl.appendChild(el);
        requestAnimationFrame(() => { el.style.opacity='1'; el.style.transform='translateY(0)'; });
        setTimeout(() => {
            el.style.opacity='0'; el.style.transform='translateY(8px)';
            el.addEventListener('transitionend', () => el.remove());
        }, 2800);
    }

    /* ---- AJAX add to cart ---- */
    const form = document.getElementById('product-add-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn     = document.getElementById('add-btn');
            const origHtml= btn.innerHTML;
            btn.disabled  = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang thêm...';

            try {
                const csrf = form.querySelector('[name="_token"]').value;
                const res  = await fetch(form.dataset.url, {
                    method : 'POST',
                    headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf },
                    body   : JSON.stringify({
                        product_id: form.dataset.productId,
                        variant_id: document.getElementById('selected-variant-id').value || undefined,
                        quantity  : parseInt(qtyInput.value) || 1,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const badge = document.getElementById('nav-cart-count');
                    if (badge) badge.textContent = data.cart_count;
                    showToast('Đã thêm vào giỏ hàng 🛒', 'success');
                } else {
                    showToast(data.message ?? 'Không thể thêm sản phẩm.', 'error');
                }
            } catch {
                showToast('Lỗi kết nối, vui lòng thử lại.', 'error');
            } finally {
                btn.disabled  = false;
                btn.innerHTML = origHtml;
            }
        });
    }

    /* ---- Auto-scroll đến phần mua hàng ---- */
    const detail = document.getElementById('product-detail');
    if (detail) {
        setTimeout(() => detail.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
    }

})();
</script>
@endpush
