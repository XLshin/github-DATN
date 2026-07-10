@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('header')
    <h1 class="h2 mb-1">Giỏ hàng</h1>
    <p class="text-muted mb-0"><span id="cart-count">{{ $items->count() }}</span> sản phẩm</p>
@endsection

@section('content')
    @if ($items->isEmpty())
        <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="bi bi-cart-x fs-5"></i>
            <span>Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></span>
        </div>
    @else
        {{-- Flash messages --}}
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
            @foreach ($errors->all() as $err)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $err }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endforeach
        @endif

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2 px-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="select-all-items" checked>
                    <label class="form-check-label fw-semibold" for="select-all-items">Chọn tất cả</label>
                </div>
            </div>
        </div>

        <div id="cart-container">
            @foreach ($items as $item)
                @php
                    $price     = (float) $item->product->price;
                    $lineTotal = $price * $item->quantity;
                    $variant   = $item->productVariant;
                    $imgSrc    = $variant?->image_path ?? $item->product->thumbnail ?? null;
                @endphp

                <div class="cart-item card shadow-sm border-0 mb-3"
                     data-item-id="{{ $item->id }}"
                     data-price="{{ $price }}"
                     data-type="{{ $item->product->product_type }}">
                    <div class="card-body py-3 px-4">
                        <div class="row align-items-center g-3">

                            {{-- ⓪ Checkbox chọn sản phẩm --}}
                            <div class="col-auto">
                                <input type="checkbox" class="form-check-input item-select" checked>
                            </div>

                            {{-- ① Hình ảnh --}}
                            <div class="col-auto">
                                @if ($imgSrc)
                                    <img src="{{ asset('storage/' . $imgSrc) }}"
                                         alt="{{ $item->product->name }}"
                                         class="rounded border"
                                         style="width:68px;height:68px;object-fit:cover;">
                                @else
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center"
                                         style="width:68px;height:68px;">
                                        <i class="bi bi-image text-muted fs-3"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- ② Tên sản phẩm + thông tin biến thể --}}
                            <div class="col">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-semibold">{{ $item->product->name }}</span>

                                    @if ($variant)
                                        {{-- Nút mũi tên toggle chi tiết biến thể --}}
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary toggle-variant py-0 px-1"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#details-{{ $item->id }}"
                                                aria-expanded="false"
                                                title="Xem chi tiết biến thể"
                                                style="border-radius:50%;width:22px;height:22px;line-height:1;flex-shrink:0">
                                            <i class="bi bi-chevron-down" style="font-size:.65rem;transition:transform .25s"></i>
                                        </button>
                                    @endif
                                </div>

                                @if ($variant)
                                    {{-- Chi tiết biến thể (ẩn mặc định) --}}
                                    <div class="collapse" id="details-{{ $item->id }}">
                                        <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                            @if ($variant->color)
                                                <span class="badge rounded-pill bg-light text-dark border"
                                                      style="font-weight:500;font-size:.75rem">
                                                    <i class="bi bi-circle-fill me-1" style="font-size:.55rem;color:#888"></i>{{ $variant->color }}
                                                </span>
                                            @endif
                                            @if ($variant->storage)
                                                <span class="badge rounded-pill bg-light text-dark border"
                                                      style="font-weight:500;font-size:.75rem">
                                                    <i class="bi bi-hdd me-1" style="font-size:.7rem"></i>{{ $variant->storage }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($variant->image_path)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $variant->image_path) }}"
                                                     alt="{{ $variant->color }} {{ $variant->storage }}"
                                                     class="rounded border"
                                                     style="height:88px;object-fit:cover;">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- ③ Đơn giá --}}
                            <div class="col-auto text-muted small d-none d-md-block" style="min-width:90px">
                                <div class="text-muted x-small mb-1" style="font-size:.75rem">Đơn giá</div>
                                {{ number_format($price, 0, ',', '.') }} đ
                            </div>

                            {{-- ④ Số lượng --}}
                            <div class="col-auto">
                                <div class="input-group input-group-sm" style="width:128px">
                                    <button class="btn btn-outline-secondary btn-qty-minus" type="button">−</button>
                                    <input type="number"
                                           class="form-control text-center qty-input"
                                           value="{{ $item->quantity }}"
                                           min="1" max="99"
                                           style="width:44px">
                                    <button class="btn btn-outline-secondary btn-qty-plus" type="button">+</button>
                                </div>
                            </div>

                            {{-- ⑤ Thành tiền --}}
                            <div class="col-auto text-end fw-semibold" style="min-width:120px">
                                <div class="text-muted x-small mb-1 d-md-none" style="font-size:.75rem">Thành tiền</div>
                                <span class="line-total text-primary">{{ number_format($lineTotal, 0, ',', '.') }}</span>
                                <span class="text-muted">đ</span>
                            </div>

                            {{-- ⑥ Nút xóa --}}
                            <div class="col-auto">
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm delete-item"
                                        title="Xóa sản phẩm">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>

                        </div>{{-- /row --}}
                    </div>{{-- /card-body --}}
                </div>{{-- /cart-item --}}
            @endforeach
        </div>{{-- /cart-container --}}

        {{-- Footer tổng tiền --}}
        <hr class="my-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Tiếp tục mua sắm
            </a>
            <div class="text-end">
                <div class="text-muted small mb-1">
                    Tổng cộng (<span id="cart-count-footer">{{ $items->count() }}</span> sản phẩm)
                </div>
                <div class="fs-3 fw-bold text-primary" id="grand-total">
                    {{ number_format($total, 0, ',', '.') }} đ
                </div>
                <button type="button" id="btn-checkout" class="btn btn-primary btn-lg mt-2">
                    <i class="bi bi-credit-card me-1"></i>Thanh toán ngay
                </button>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function fmt(n) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(n));
    }

    /** Tính lại tổng theo các dòng đang được chọn (checkbox tick) */
    function recalcGrandTotal() {
        let sum = 0;
        let selectedCount = 0;
        document.querySelectorAll('.cart-item').forEach(row => {
            if (!row.querySelector('.item-select')?.checked) return;
            const price = parseFloat(row.dataset.price) || 0;
            const qty   = parseInt(row.querySelector('.qty-input').value) || 0;
            sum += price * qty;
            selectedCount++;
        });
        document.getElementById('grand-total').textContent = fmt(sum) + ' đ';
        const countEl = document.getElementById('cart-count-footer');
        if (countEl) countEl.textContent = selectedCount;

        const btnCheckout = document.getElementById('btn-checkout');
        if (btnCheckout) btnCheckout.disabled = selectedCount === 0;

        syncSelectAllState();
    }

    /** Cập nhật trạng thái checkbox "Chọn tất cả" (tick / bỏ tick / indeterminate) */
    function syncSelectAllState() {
        const selectAll = document.getElementById('select-all-items');
        if (!selectAll) return;
        const boxes = Array.from(document.querySelectorAll('.item-select'));
        const checkedCount = boxes.filter(b => b.checked).length;
        selectAll.checked = boxes.length > 0 && checkedCount === boxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
    }

    /** Cập nhật thành tiền của 1 dòng */
    function refreshLineTotal(row) {
        const price = parseFloat(row.dataset.price) || 0;
        const qty   = parseInt(row.querySelector('.qty-input').value) || 0;
        row.querySelector('.line-total').textContent = fmt(price * qty);
        recalcGrandTotal();
    }

    /** Gửi số lượng mới lên server; nếu vượt tồn kho thì khôi phục lại giá trị cũ */
    function persistQty(row) {
        const input  = row.querySelector('.qty-input');
        const itemId = row.dataset.itemId;
        const qty    = parseInt(input.value) || 1;

        fetch('{{ route('cart.update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept'      : 'application/json',
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
        .catch(() => {});
    }

    const timers = {};
    function debounce(key, fn, ms = 600) {
        clearTimeout(timers[key]);
        timers[key] = setTimeout(fn, ms);
    }

    /** Chọn tất cả */
    document.getElementById('select-all-items')?.addEventListener('change', (e) => {
        document.querySelectorAll('.item-select').forEach(box => box.checked = e.target.checked);
        recalcGrandTotal();
    });

    /** Nút thanh toán: mang theo danh sách SP đã chọn sang trang checkout */
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

    /** Khởi tạo từng dòng giỏ hàng */
    document.querySelectorAll('.cart-item').forEach(row => {
        const id    = row.dataset.itemId;
        const input = row.querySelector('.qty-input');
        input.dataset.confirmedQty = input.value;

        row.querySelector('.item-select')?.addEventListener('change', recalcGrandTotal);

        /* --- Nút + --- */
        row.querySelector('.btn-qty-plus')?.addEventListener('click', () => {
            const v = parseInt(input.value) || 1;
            input.value = Math.min(99, v + 1);
            refreshLineTotal(row);
            debounce(id, () => persistQty(row));
        });

        /* --- Nút − --- */
        row.querySelector('.btn-qty-minus')?.addEventListener('click', () => {
            const v = parseInt(input.value) || 1;
            input.value = Math.max(1, v - 1);
            refreshLineTotal(row);
            debounce(id, () => persistQty(row));
        });

        /* --- Nhập tay --- */
        input.addEventListener('input', () => {
            let v = parseInt(input.value);
            if (isNaN(v) || v < 1) v = 1;
            if (v > 99) v = 99;
            input.value = v;
            refreshLineTotal(row);
            debounce(id, () => persistQty(row));
        });

        /* --- Xoay mũi tên khi expand/collapse --- */
        const collapseEl = document.getElementById('details-' + id);
        if (collapseEl) {
            collapseEl.addEventListener('show.bs.collapse', () => {
                row.querySelector('.toggle-variant i')
                   ?.classList.replace('bi-chevron-down', 'bi-chevron-up');
            });
            collapseEl.addEventListener('hide.bs.collapse', () => {
                row.querySelector('.toggle-variant i')
                   ?.classList.replace('bi-chevron-up', 'bi-chevron-down');
            });
        }

        /* --- Xóa sản phẩm --- */
        row.querySelector('.delete-item')?.addEventListener('click', () => {
            if (!confirm('Xóa sản phẩm này khỏi giỏ hàng?')) return;

            fetch('{{ route('cart.remove') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                    'Accept'      : 'application/json',
                },
                body: JSON.stringify({ cart_item_id: id }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    row.remove();
                    recalcGrandTotal();

                    const remaining = document.querySelectorAll('.cart-item').length;
                    const cartCountEl = document.getElementById('cart-count');
                    if (cartCountEl) cartCountEl.textContent = remaining;

                    if (remaining === 0) {
                        document.getElementById('cart-container').innerHTML =
                            '<div class="alert alert-info"><i class="bi bi-cart-x me-2"></i>'
                            + 'Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></div>';
                        document.querySelector('.btn-primary.btn-lg')?.remove();
                        document.getElementById('grand-total')?.closest('.text-end')?.remove();
                        document.querySelector('hr.my-3')?.remove();
                    }
                }
            })
            .catch(() => alert('Có lỗi xảy ra, vui lòng thử lại.'));
        });
    });

    recalcGrandTotal();

})();
</script>
@endpush

