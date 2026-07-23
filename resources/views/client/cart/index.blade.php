@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('header')
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h2 mb-1">Giỏ hàng</h1>
            <p class="text-muted mb-0"><span id="cart-count">{{ $items->count() }}</span> sản phẩm trong giỏ</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua
        </a>
    </div>
@endsection

@push('styles')
<style>
    .cart-page { background: #f5f7fb; padding: 22px 0 44px; }
    .cart-panel { background: #fff; border: 1px solid #e6ebf2; border-radius: 8px; }
    .cart-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-bottom: 1px solid #eef2f7; }
    .cart-item { padding: 16px; border-bottom: 1px solid #eef2f7; }
    .cart-item:last-child { border-bottom: 0; }
    .cart-thumb { width: 86px; height: 86px; border-radius: 8px; border: 1px solid #e5eaf2; background: #f8fafc; object-fit: contain; padding: 6px; }
    .cart-placeholder { width: 86px; height: 86px; border-radius: 8px; border: 1px solid #e5eaf2; background: #f8fafc; display: grid; place-items: center; color: #94a3b8; }
    .variant-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 9px; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: .8125rem; }
    .qty-control { width: 132px; }
    .qty-fixed { width: 132px; height: 32px; border: 1px solid #dbe3ee; border-radius: 6px; background: #f8fafc; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #475569; }
    .cart-line-actions { min-width: 168px; display: flex; align-items: center; justify-content: flex-end; gap: 12px; }
    .delete-item { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; }
    .summary-card { position: sticky; top: 18px; background: #fff; border: 1px solid #e6ebf2; border-radius: 8px; }
    .summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; }
    .summary-total { border-top: 1px solid #e6ebf2; margin-top: 8px; padding-top: 14px; }
    .empty-cart { background: #fff; border: 1px solid #e6ebf2; border-radius: 8px; padding: 42px 20px; text-align: center; }
    @media (max-width: 767.98px) {
        .cart-item { padding: 14px; }
        .cart-thumb, .cart-placeholder { width: 74px; height: 74px; }
        .cart-line-actions { min-width: 0; justify-content: space-between; }
        .summary-card { position: static; }
    }
</style>
@endpush

@section('content')
<div class="cart-page">
    <div class="container">
        @if ($items->isEmpty())
            <div class="empty-cart">
                <svg width="180" height="140" viewBox="0 0 180 140" class="mb-3" aria-hidden="true">
                    <ellipse cx="90" cy="126" rx="60" ry="8" fill="#eef2f7"/>
                    <rect x="40" y="46" width="100" height="62" rx="10" fill="#eef5ff"/>
                    <path d="M60 46 L66 24 H114 L120 46" fill="none" stroke="#0d6efd" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="68" cy="118" r="9" fill="#0d6efd"/>
                    <circle cx="112" cy="118" r="9" fill="#0d6efd"/>
                    <path d="M52 58 L64 90 H116 L128 58" fill="none" stroke="#9fc2ff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="80" y1="66" x2="80" y2="80" stroke="#9fc2ff" stroke-width="4" stroke-linecap="round"/>
                    <line x1="100" y1="66" x2="100" y2="80" stroke="#9fc2ff" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <h2 class="h4 mb-2">Giỏ hàng đang trống</h2>
                <p class="text-muted mb-4">Chọn thêm vài món ngon lành rồi quay lại thanh toán nhé.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="bi bi-bag me-1"></i> Mua sắm ngay
                </a>
            </div>
        @else
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <section class="cart-panel">
                        <div class="cart-toolbar">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="select-all-items" checked>
                                <label class="form-check-label fw-semibold" for="select-all-items">Chọn tất cả</label>
                            </div>
                            <span class="text-muted small">Có thể chọn một phần giỏ hàng để thanh toán</span>
                        </div>

                        <div id="cart-container">
                            @foreach ($items as $item)
                                @php
                                    $variant = $item->productVariant;
                                    $unitPrice = (float) ($item->product?->price ?? 0) + (float) ($variant?->additional_price ?? 0);
                                    $lineTotal = $unitPrice * (int) $item->quantity;
                                    $normalizeImagePath = fn ($path) => str_starts_with(ltrim((string) $path, '/'), 'storage/')
                                        ? substr(ltrim((string) $path, '/'), 8)
                                        : ltrim((string) $path, '/');
                                    $imgSrc = collect([
                                        $item->product?->productGroup?->images?->first()?->image_path,
                                        $item->product?->thumbnail,
                                        $item->product?->images?->first()?->image_path,
                                        $variant?->image_path,
                                        $variant?->images?->first()?->image_path,
                                    ])->filter()->first(function ($path) use ($normalizeImagePath) {
                                        $cleanImagePath = ltrim((string) $path, '/');
                                        $isRemoteImage = str_starts_with($cleanImagePath, 'http://') || str_starts_with($cleanImagePath, 'https://');

                                        return $isRemoteImage || Storage::disk('public')->exists($normalizeImagePath($path));
                                    });
                                    $imageUrl = null;
                                    if ($imgSrc) {
                                        $cleanImagePath = $normalizeImagePath($imgSrc);
                                        $isRemoteImage = str_starts_with($cleanImagePath, 'http://') || str_starts_with($cleanImagePath, 'https://');
                                        $imageUrl = $isRemoteImage
                                            ? $cleanImagePath
                                            : Storage::url($cleanImagePath);
                                    }
                                @endphp

                                <article class="cart-item"
                                     data-item-id="{{ $item->id }}"
                                     data-price="{{ $unitPrice }}"
                                     data-type="{{ $item->product->product_type }}">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <input type="checkbox" class="form-check-input item-select" checked aria-label="Chọn sản phẩm">
                                        </div>

                                        <div class="col-auto">
                                            @if ($imageUrl)
                                                <img
                                                    src="{{ $imageUrl }}"
                                                    alt="{{ $item->product->name }}"
                                                    class="cart-thumb"
                                                    onerror="this.classList.add('d-none'); this.nextElementSibling?.classList.remove('d-none');">
                                                <div class="cart-placeholder d-none"><i class="bi bi-image fs-3"></i></div>
                                            @else
                                                <div class="cart-placeholder"><i class="bi bi-image fs-3"></i></div>
                                            @endif
                                        </div>

                                        <div class="col">
                                            <a href="{{ route('products.show', $item->product) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $item->product->name }}
                                            </a>
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                @if($variant?->color)
                                                    <span class="variant-chip"><i class="bi bi-palette"></i>{{ $variant->color }}</span>
                                                @endif
                                                @if($item->product?->storage)
                                                    <span class="variant-chip"><i class="bi bi-device-ssd"></i>{{ $item->product->storage }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mt-2">
                                                Đơn giá: <span class="fw-semibold text-dark">{{ number_format($unitPrice, 0, ',', '.') }} đ</span>
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-auto">
                                            <div class="input-group input-group-sm qty-control">
                                                <button class="btn btn-outline-secondary btn-qty-minus" type="button">-</button>
                                                <input type="number" class="form-control text-center qty-input" value="{{ $item->quantity }}" min="1" max="99">
                                                <button class="btn btn-outline-secondary btn-qty-plus" type="button">+</button>
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-auto text-end">
                                            <div class="cart-line-actions">
                                                <div class="line-total-wrap fw-bold text-primary text-nowrap">
                                                    <span class="line-total">{{ number_format($lineTotal, 0, ',', '.') }}</span> đ
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete-item" title="Xóa sản phẩm" aria-label="Xóa sản phẩm">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="col-lg-4">
                    <aside class="summary-card p-3 p-md-4">
                        <h2 class="h5 mb-3">Tóm tắt giỏ hàng</h2>
                        <div class="summary-row text-muted">
                            <span>Sản phẩm đã chọn</span>
                            <span><span id="cart-count-footer">{{ $items->count() }}</span> món</span>
                        </div>
                        <div class="summary-row text-muted">
                            <span>Tạm tính</span>
                            <span id="summary-subtotal">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="summary-row summary-total align-items-center">
                            <span class="fw-semibold">Tổng cộng</span>
                            <span class="fs-3 fw-bold text-primary" id="grand-total">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                        <button type="button" id="btn-checkout" class="btn btn-primary btn-lg w-100 mt-3">
                            <i class="bi bi-credit-card me-1"></i> Thanh toán ngay
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-light w-100 mt-2">Tiếp tục mua sắm</a>
                    </aside>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function fmt(n) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(n));
    }

    function recalcGrandTotal() {
        let sum = 0;
        let selectedCount = 0;

        document.querySelectorAll('.cart-item').forEach(row => {
            if (!row.querySelector('.item-select')?.checked) return;
            const price = parseFloat(row.dataset.price) || 0;
            const qty = parseInt(row.querySelector('.qty-input').value) || 0;
            sum += price * qty;
            selectedCount++;
        });

        const totalText = fmt(sum) + ' đ';
        document.getElementById('grand-total').textContent = totalText;
        document.getElementById('summary-subtotal').textContent = totalText;

        const countEl = document.getElementById('cart-count-footer');
        if (countEl) countEl.textContent = selectedCount;

        const btnCheckout = document.getElementById('btn-checkout');
        if (btnCheckout) btnCheckout.disabled = selectedCount === 0;

        syncSelectAllState();
    }

    function syncSelectAllState() {
        const selectAll = document.getElementById('select-all-items');
        if (!selectAll) return;
        const boxes = Array.from(document.querySelectorAll('.item-select'));
        const checkedCount = boxes.filter(b => b.checked).length;
        selectAll.checked = boxes.length > 0 && checkedCount === boxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
    }

    function refreshLineTotal(row) {
        const price = parseFloat(row.dataset.price) || 0;
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        row.querySelector('.line-total').textContent = fmt(price * qty);
        recalcGrandTotal();
    }

    function persistQty(row) {
        const input = row.querySelector('.qty-input');
        const itemId = row.dataset.itemId;
        const qty = parseInt(input.value) || 1;

        fetch('{{ route('cart.update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ cart_item_id: itemId, quantity: qty }),
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));

            if (!r.ok || !data.success) {
                input.value = data.max_quantity > 0 ? data.max_quantity : (input.dataset.confirmedQty || 1);
                refreshLineTotal(row);
                alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                return;
            }

            input.dataset.confirmedQty = input.value;
        })
        .catch(() => alert('Có lỗi xảy ra, vui lòng thử lại.'));
    }

    const timers = {};
    function debounce(key, fn, ms = 600) {
        clearTimeout(timers[key]);
        timers[key] = setTimeout(fn, ms);
    }

    document.getElementById('select-all-items')?.addEventListener('change', (e) => {
        document.querySelectorAll('.item-select').forEach(box => box.checked = e.target.checked);
        recalcGrandTotal();
    });

    document.getElementById('btn-checkout')?.addEventListener('click', () => {
        const ids = Array.from(document.querySelectorAll('.cart-item'))
            .filter(row => row.querySelector('.item-select')?.checked)
            .map(row => row.dataset.itemId);

        if (ids.length === 0) {
            alert('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
            return;
        }

        window.location.href = '{{ route('checkout.show') }}?items=' + ids.join(',');
    });

    document.querySelectorAll('.cart-item').forEach(row => {
        const id = row.dataset.itemId;
        const input = row.querySelector('.qty-input');
        input.dataset.confirmedQty = input.value;

        row.querySelector('.item-select')?.addEventListener('change', recalcGrandTotal);

        row.querySelector('.btn-qty-plus')?.addEventListener('click', () => {
            const v = parseInt(input.value) || 1;
            input.value = Math.min(99, v + 1);
            refreshLineTotal(row);
            debounce(id, () => persistQty(row));
        });

        row.querySelector('.btn-qty-minus')?.addEventListener('click', () => {
            const v = parseInt(input.value) || 1;
            input.value = Math.max(1, v - 1);
            refreshLineTotal(row);
            debounce(id, () => persistQty(row));
        });

        input.addEventListener('input', () => {
            let v = parseInt(input.value);
            if (isNaN(v) || v < 1) v = 1;
            if (v > 99) v = 99;
            input.value = v;
            refreshLineTotal(row);
            debounce(id, () => persistQty(row));
        });

        row.querySelector('.delete-item')?.addEventListener('click', () => {
            if (!confirm('Xóa sản phẩm này khỏi giỏ hàng?')) return;

            fetch('{{ route('cart.remove') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ cart_item_id: id }),
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                row.remove();
                recalcGrandTotal();

                const remaining = document.querySelectorAll('.cart-item').length;
                const cartCountEl = document.getElementById('cart-count');
                if (cartCountEl) cartCountEl.textContent = remaining;

                if (remaining === 0) {
                    location.reload();
                }
            })
            .catch(() => alert('Có lỗi xảy ra, vui lòng thử lại.'));
        });
    });

    recalcGrandTotal();
})();
</script>
@endpush
