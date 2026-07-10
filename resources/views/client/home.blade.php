@extends('layouts.app')

@section('title', 'Trang chủ')

@section('header')
<h1 class="h2 mb-1">Điện thoại chính hãng</h1>
<p class="text-muted mb-0">Khám phá sản phẩm mới nhất tại Byte Zone Store</p>
@endsection

@section('content')
@if ($products->isEmpty())
<div class="alert alert-info">
    Chưa có sản phẩm nào.
</div>
@else
<div class="row g-4">
    @foreach ($products as $product)
    <div class="col-md-6 col-lg-4">
        <article class="product-card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <h2 class="h5 mb-2">
                    <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark">
                        {{ $product->name }}
                    </a>
                </h2>

                <p class="text-muted small flex-grow-1">
                    {{ \Illuminate\Support\Str::limit($product->description, 100) }}
                </p>

                <div class="price-tag mb-3">
                    {{ number_format($product->price, 0, ',', '.') }} đ
                </div>

                @guest
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                    Đăng nhập để mua
                </a>
                @endguest

                @auth
                @if (auth()->user()->role === 'customer')
                <form class="add-to-cart-form d-flex gap-2"
                      data-product-id="{{ $product->id }}"
                      data-url="{{ route('cart.add') }}">
                    @csrf

                    <input
                        type="number"
                        name="quantity"
                        value="1"
                        min="1"
                        class="form-control form-control-sm"
                        style="width: 70px;">

                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-cart-plus"></i>
                        Thêm giỏ
                    </button>
                </form>
                @elseif (in_array(auth()->user()->role, ['admin', 'staff'], true))
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-speedometer2"></i>
                    Vào trang quản trị
                </a>
                @else
                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                    Không có quyền mua hàng
                </button>
                @endif
                @endauth
            </div>
        </article>
    </div>
    @endforeach
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ---- Toast container ---- */
    const toastContainer = document.createElement('div');
    toastContainer.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none';
    document.body.appendChild(toastContainer);

    function showToast(msg, type) {
        const el = document.createElement('div');
        const bg  = type === 'error' ? '#dc3545' : '#198754';
        const ico = type === 'error' ? '✕' : '✓';
        el.style.cssText = `background:${bg};color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;font-weight:500;
            box-shadow:0 4px 14px rgba(0,0,0,.25);display:flex;align-items:center;gap:10px;
            opacity:0;transform:translateY(16px);transition:opacity .25s,transform .25s;pointer-events:auto;min-width:220px`;
        el.innerHTML = `<span style="font-size:18px;line-height:1">${ico}</span>${msg}`;
        toastContainer.appendChild(el);
        requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            el.addEventListener('transitionend', () => el.remove());
        }, 2800);
    }

    /* ---- Intercept tất cả form "Thêm giỏ" ---- */
    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!form.classList.contains('add-to-cart-form')) return;
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        try {
            const csrf  = form.querySelector('[name="_token"]').value;
            const url   = form.dataset.url;
            const qty   = parseInt(form.querySelector('[name="quantity"]').value) || 1;
            const pid   = form.dataset.productId;

            const res  = await fetch(url, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body   : JSON.stringify({ product_id: pid, quantity: qty }),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                /* Cập nhật badge giỏ hàng */
                const badge = document.getElementById('nav-cart-count');
                if (badge) badge.textContent = data.cart_count;

                showToast('Đã thêm vào giỏ hàng 🛒', 'success');
            } else {
                showToast(data.message ?? 'Không thể thêm sản phẩm.', 'error');
            }
        } catch (err) {
            showToast('Lỗi kết nối, vui lòng thử lại.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    });
})();
</script>
@endpush