@extends('layouts.app')

@section('title', 'Thanh toán')

@section('header')
    <h1 class="h2 mb-1">Thanh toán</h1>
    <p class="text-muted mb-0">Hoàn tất thông tin giao hàng</p>
@endsection

@section('content')
    @if ($items->isEmpty())
        <div class="alert alert-warning">Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST" action="{{ route('checkout.process') }}" class="needs-validation" novalidate>
                            @csrf

                            @foreach($selectedIds ?? [] as $selectedId)
                                <input type="hidden" name="cart_item_ids[]" value="{{ $selectedId }}">
                            @endforeach

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="buyer-proxy-toggle" name="buyer_type" value="proxy" {{ old('buyer_type') === 'proxy' ? 'checked' : '' }}>
                                <label class="form-check-label" for="buyer-proxy-toggle">Đặt hộ cho người khác</label>
                            </div>

                            <div id="buyer-proxy-fields" class="d-none border rounded p-3 mb-3 bg-light">
                                <div class="mb-3">
                                    <label class="form-label">Họ tên người đặt (bạn)</label>
                                    <input type="text" name="buyer_name" class="form-control" value="{{ old('buyer_name', auth()->user()->name ?? '') }}">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">SĐT người đặt (bạn)</label>
                                    <input type="text" name="buyer_phone" class="form-control" value="{{ old('buyer_phone', auth()->user()->phone ?? '') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Họ tên người nhận</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ giao hàng</label>
                                <textarea name="shipping_address" class="form-control" rows="3" required>{{ old('shipping_address', auth()->user()->address ?? '') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Voucher</label>
                                <select name="coupon_id" class="form-select">
                                    <option value="">-- Không chọn voucher --</option>
                                    @forelse($availableCoupons ?? [] as $coupon)
                                        <option value="{{ $coupon->id }}" {{ old('coupon_id') == $coupon->id ? 'selected' : '' }}>
                                            {{ $coupon->code }} -
                                            @if($coupon->discount_type === 'percent')
                                                {{ $coupon->discount_value }}%
                                            @else
                                                {{ number_format($coupon->discount_value, 0, ',', '.') }} đ
                                            @endif
                                            (Tối thiểu: {{ number_format($coupon->min_order_amount, 0, ',', '.') }} đ)
                                        </option>
                                    @empty
                                        <option value="" disabled>Không có voucher nào</option>
                                    @endforelse
                                </select>
                                @error('coupon_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dùng điểm</label>
                                <div class="input-group">
                                    <input type="number" name="points_to_use" class="form-control" value="{{ old('points_to_use', 0) }}" min="0" max="{{ auth()->user()->points ?? 0 }}">
                                    <span class="input-group-text">đ (Bạn có: {{ number_format(auth()->user()->points ?? 0) }} điểm)</span>
                                </div>
                                @error('points_to_use')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 d-none" id="checkout-preview-error">
                                <div class="text-danger small mt-1"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phương thức thanh toán</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                    <option value="card">Thẻ (giả lập)</option>
                                    <option value="bank_transfer">Chuyển khoản</option>
                                    <option value="momo">Momo (giả lập)</option>
                                    <option value="vnpay">VNPAY (giả lập)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Đặt hàng</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Đơn hàng</div>
                    <ul class="list-group list-group-flush">
                        @foreach ($items as $item)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $item->product->name }} x{{ $item->quantity }}</span>
                                <span>{{ number_format($item->product->price * $item->quantity, 0, ',', '.') }} đ</span>
                            </li>
                        @endforeach
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tổng hàng</span>
                            <span id="subtotal">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between text-success d-none" id="coupon-row">
                            <span>Giảm voucher</span>
                            <span id="coupon-discount">-0 đ</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between text-success d-none" id="points-row">
                            <span>Giảm điểm</span>
                            <span id="points-discount">-0 đ</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Tổng cộng</span>
                            <span class="text-primary" id="total">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
    (function(){
        const proxyToggle = document.getElementById('buyer-proxy-toggle');
        const proxyFields = document.getElementById('buyer-proxy-fields');
        if (proxyToggle) {
            proxyToggle.addEventListener('change', function () {
                proxyFields.classList.toggle('d-none', !proxyToggle.checked);
            });
            proxyFields.classList.toggle('d-none', !proxyToggle.checked);
        }

        const selectedItemIds = @json($selectedIds ?? []);
        const couponSelect = document.querySelector('select[name="coupon_id"]');
        const pointsInput = document.querySelector('input[name="points_to_use"]');
        const couponRow = document.getElementById('coupon-row');
        const pointsRow = document.getElementById('points-row');
        const couponDiscountEl = document.getElementById('coupon-discount');
        const pointsDiscountEl = document.getElementById('points-discount');
        const totalEl = document.getElementById('total');
        const subtotalEl = document.getElementById('subtotal');

        function formatVND(num){
            return new Intl.NumberFormat('vi-VN').format(Math.round(num)) + ' đ';
        }

        let timer = null;
        function preview(){
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
            const payload = new URLSearchParams();
            payload.append('coupon_id', couponSelect ? couponSelect.value : '');
            payload.append('points_to_use', pointsInput ? pointsInput.value : 0);
            selectedItemIds.forEach(id => payload.append('cart_item_ids[]', id));

            fetch('{{ route('checkout.preview') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: payload.toString()
            }).then(async r => {
                const data = await r.json();

                if (!r.ok) {
                    showPreviewError(data?.errors?.coupon_id?.[0] || data?.message || 'Voucher không hợp lệ.');
                    resetTotals();
                    return;
                }

                hidePreviewError();

                if (data.coupon_discount && data.coupon_discount > 0) {
                    couponRow.classList.remove('d-none');
                    couponDiscountEl.textContent = '-' + new Intl.NumberFormat('vi-VN').format(data.coupon_discount) + ' đ';
                } else {
                    couponRow.classList.add('d-none');
                }

                if (data.points_discount && data.points_discount > 0) {
                    pointsRow.classList.remove('d-none');
                    pointsDiscountEl.textContent = '-' + new Intl.NumberFormat('vi-VN').format(data.points_discount) + ' đ';
                } else {
                    pointsRow.classList.add('d-none');
                }

                subtotalEl.textContent = formatVND(data.subtotal);
                totalEl.textContent = formatVND(data.total);
            }).catch(() => {
                showPreviewError('Không thể kiểm tra voucher hiện tại.');
                resetTotals();
            });
        }

        function showPreviewError(message) {
            const errorContainer = document.getElementById('checkout-preview-error');
            if (!errorContainer) return;
            errorContainer.querySelector('div').textContent = message;
            errorContainer.classList.remove('d-none');
        }

        function hidePreviewError() {
            const errorContainer = document.getElementById('checkout-preview-error');
            if (!errorContainer) return;
            errorContainer.querySelector('div').textContent = '';
            errorContainer.classList.add('d-none');
        }

        function resetTotals() {
            subtotalEl.textContent = formatVND({{ $total }});
            totalEl.textContent = formatVND({{ $total }});
            couponRow.classList.add('d-none');
            pointsRow.classList.add('d-none');
        }

        function schedulePreview(){
            if (timer) clearTimeout(timer);
            timer = setTimeout(preview, 350);
        }

        if (couponSelect) couponSelect.addEventListener('change', schedulePreview);
        if (pointsInput) pointsInput.addEventListener('input', schedulePreview);

        schedulePreview();
    })();
    </script>
    @endpush
@endsection
