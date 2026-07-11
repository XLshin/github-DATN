@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('header')
    <h1 class="h2 mb-1">Giỏ hàng</h1>
    <p class="text-muted mb-0" id="cart-item-count">{{ $items->count() }} sản phẩm</p>
@endsection

@section('content')
    @if ($items->isEmpty())
        <div class="alert alert-info">Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></div>
    @else
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="cart-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>Sản phẩm</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-center" style="width:140px">SL</th>
                            <th class="text-end">Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $unitPrice = (float) $item->product->price + (float) ($item->variant->additional_price ?? 0);
                            @endphp
                            <tr class="cart-row" data-cart-item-id="{{ $item->id }}" data-unit-price="{{ $unitPrice }}">
                                <td>
                                    <input type="checkbox" class="form-check-input cart-select" value="{{ $item->id }}" checked>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name }}</div>
                                    @if($item->variant)
                                        <div class="text-muted small">
                                            Màu: {{ $item->variant->color }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end unit-price">{{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm justify-content-center" style="max-width:130px; margin:auto">
                                        <button type="button" class="btn btn-outline-secondary qty-decrease">-</button>
                                        <input type="number" class="form-control text-center qty-input" value="{{ $item->quantity }}" min="0">
                                        <button type="button" class="btn btn-outline-secondary qty-increase">+</button>
                                    </div>
                                </td>
                                <td class="text-end row-total">{{ number_format($unitPrice * $item->quantity, 0, ',', '.') }} đ</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm cart-remove">Xóa</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>
            <div class="text-end">
                <div class="fs-5 fw-bold">Tạm tính (<span id="selected-count">{{ $items->count() }}</span> sản phẩm chọn): <span id="cart-selected-total">{{ number_format($total, 0, ',', '.') }} đ</span></div>
                <button type="button" class="btn btn-primary mt-2" id="checkout-btn">Thanh toán</button>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    const table = document.getElementById('cart-table');
    if (!table) return;

    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
    const selectAll = document.getElementById('select-all');
    const selectedCountEl = document.getElementById('selected-count');
    const selectedTotalEl = document.getElementById('cart-selected-total');
    const checkoutBtn = document.getElementById('checkout-btn');

    function formatVND(num) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(num)) + ' đ';
    }

    function recalcSelected() {
        let count = 0;
        let total = 0;

        table.querySelectorAll('.cart-row').forEach(function (row) {
            const checkbox = row.querySelector('.cart-select');
            if (checkbox && checkbox.checked) {
                count += 1;
                total += parseFloat(row.dataset.unitPrice) * parseInt(row.querySelector('.qty-input').value || 0, 10);
            }
        });

        selectedCountEl.textContent = count;
        selectedTotalEl.textContent = formatVND(total);
        checkoutBtn.disabled = count === 0;
    }

    function updateRowTotal(row) {
        const unitPrice = parseFloat(row.dataset.unitPrice);
        const qty = parseInt(row.querySelector('.qty-input').value || 0, 10);
        row.querySelector('.row-total').textContent = formatVND(unitPrice * qty);
    }

    function ajax(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body.toString(),
        }).then(r => r.json());
    }

    function updateCartBadge(count) {
        document.querySelectorAll('.total-items').forEach(function (el) {
            el.textContent = count;
        });
    }

    table.addEventListener('click', function (e) {
        const row = e.target.closest('.cart-row');
        if (!row) return;
        const cartItemId = row.dataset.cartItemId;

        if (e.target.classList.contains('qty-increase') || e.target.classList.contains('qty-decrease')) {
            const input = row.querySelector('.qty-input');
            let qty = parseInt(input.value || 0, 10);
            qty = e.target.classList.contains('qty-increase') ? qty + 1 : Math.max(0, qty - 1);
            input.value = qty;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (e.target.classList.contains('cart-remove')) {
            const body = new URLSearchParams();
            body.append('cart_item_id', cartItemId);
            ajax('{{ route('cart.remove') }}', body).then(function (data) {
                row.remove();
                updateCartBadge(data.cart_count);
                recalcSelected();
                document.getElementById('cart-item-count').textContent = table.querySelectorAll('.cart-row').length + ' sản phẩm';
            });
        }
    });

    table.addEventListener('change', function (e) {
        const row = e.target.closest('.cart-row');

        if (e.target.classList.contains('cart-select')) {
            recalcSelected();
            return;
        }

        if (!row) return;
        const cartItemId = row.dataset.cartItemId;

        if (e.target.classList.contains('qty-input')) {
            const qty = Math.max(0, parseInt(e.target.value || 0, 10));
            e.target.value = qty;

            const body = new URLSearchParams();
            body.append('cart_item_id', cartItemId);
            body.append('quantity', qty);

            ajax('{{ route('cart.update') }}', body).then(function (data) {
                updateCartBadge(data.cart_count);

                if (data.removed) {
                    row.remove();
                    document.getElementById('cart-item-count').textContent = table.querySelectorAll('.cart-row').length + ' sản phẩm';
                } else {
                    updateRowTotal(row);
                }

                recalcSelected();
            });
        }
    });

    selectAll.addEventListener('change', function () {
        table.querySelectorAll('.cart-select').forEach(function (cb) {
            cb.checked = selectAll.checked;
        });
        recalcSelected();
    });

    checkoutBtn.addEventListener('click', function () {
        const ids = Array.from(table.querySelectorAll('.cart-select:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        const params = new URLSearchParams();
        ids.forEach(id => params.append('items[]', id));
        window.location.href = '{{ route('checkout.show') }}?' + params.toString();
    });

    recalcSelected();
})();
</script>
@endpush
