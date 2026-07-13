@extends('layouts.app')

@section('title', 'Thanh toán')

@section('header')
    <h1 class="h2 mb-1">Thanh toán</h1>
    <p class="text-muted mb-0">Hoàn tất thông tin giao hàng</p>
@endsection

@push('styles')
<style>
    .pm-list { display: flex; flex-direction: column; gap: 10px; }

    .pm-card {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e3e6ea;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .pm-card:hover { border-color: #9fc2ff; background: #f8faff; }

    .pm-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #f1f3f5;
        color: #495057;
    }

    .pm-icon--cod   { background: #fff1e0; color: #d9822b; }
    .pm-icon--bank  { background: #e3f3ec; color: #1a9a6c; }
    .pm-icon--card  { background: #f1f0ff; color: #5b4fd6; }

    .pm-icon--brand {
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        text-align: center;
        box-shadow: inset 0 -2px 4px rgba(0,0,0,.15);
    }

    .pm-text { flex: 1 1 auto; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .pm-title { font-weight: 600; }
    .pm-subtitle { font-size: .8125rem; color: #6c757d; }

    .pm-networks { display: flex; gap: 4px; margin-top: 2px; }
    .pm-network-badge {
        font-size: .625rem;
        font-weight: 700;
        letter-spacing: .3px;
        padding: 1px 6px;
        border-radius: 4px;
        color: #fff;
        line-height: 1.5;
    }

    .pm-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #ced4da;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color .15s ease;
    }

    .pm-dot::after {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #0d6efd;
        transform: scale(0);
        transition: transform .15s ease;
    }

    .pm-radio:checked + .pm-card {
        border-color: #0d6efd;
        background: #eef5ff;
        box-shadow: 0 0 0 1px #0d6efd inset, 0 4px 10px rgba(13,110,253,.12);
    }

    .pm-radio:checked + .pm-card .pm-dot { border-color: #0d6efd; }
    .pm-radio:checked + .pm-card .pm-dot::after { transform: scale(1); }

    .pm-radio:focus-visible + .pm-card { outline: 2px solid #0d6efd; outline-offset: 2px; }
</style>
@endpush

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
                                <label class="form-label fw-semibold d-block">Người mua hàng</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="buyer_type" id="buyer_type_self" value="self"
                                               {{ old('buyer_type', 'self') === 'self' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="buyer_type_self">Chính tôi</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="buyer_type" id="buyer_type_proxy" value="proxy"
                                               {{ old('buyer_type') === 'proxy' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="buyer_type_proxy">Mua hộ người khác</label>
                                    </div>
                                </div>
                            </div>

                            <div id="proxy-buyer-fields" class="{{ old('buyer_type') === 'proxy' ? '' : 'd-none' }}">
                                <div class="mb-3">
                                    <label class="form-label">Tên người đặt mua</label>
                                    <input type="text" name="buyer_name" class="form-control" value="{{ old('buyer_name') }}">
                                    @error('buyer_name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SĐT người đặt mua</label>
                                    <input type="text" name="buyer_phone" class="form-control" value="{{ old('buyer_phone') }}">
                                    @error('buyer_phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="text-muted small mb-3">
                                    Thông tin bên dưới ("Họ tên người nhận") là người sẽ nhận hàng — có thể khác với bạn.
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
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Phương thức thanh toán</label>
                                <div class="pm-list" id="payment-methods">

                                    {{-- COD --}}
                                    <input type="radio" class="pm-radio btn-check" name="payment_method" id="pm_cod" value="cod" checked>
                                    <label class="pm-card" for="pm_cod">
                                        <span class="pm-icon pm-icon--cod"><i class="bi bi-truck"></i></span>
                                        <span class="pm-text">
                                            <span class="pm-title">Thanh toán khi nhận hàng (COD)</span>
                                            <span class="pm-subtitle">Trả tiền mặt cho nhân viên giao hàng</span>
                                        </span>
                                        <span class="pm-dot"></span>
                                    </label>

                                    {{-- Bank Transfer --}}
                                    <input type="radio" class="pm-radio btn-check" name="payment_method" id="pm_bank" value="bank_transfer">
                                    <label class="pm-card" for="pm_bank">
                                        <span class="pm-icon pm-icon--bank"><i class="bi bi-bank2"></i></span>
                                        <span class="pm-text">
                                            <span class="pm-title">Chuyển khoản ngân hàng</span>
                                            <span class="pm-subtitle">Nhận thông tin tài khoản + QR sau khi đặt hàng</span>
                                        </span>
                                        <span class="pm-dot"></span>
                                    </label>

                                    {{-- MoMo --}}
                                    <input type="radio" class="pm-radio btn-check" name="payment_method" id="pm_momo" value="momo">
                                    <label class="pm-card" for="pm_momo">
                                        <span class="pm-icon pm-icon--brand" style="background:#AE2070">M</span>
                                        <span class="pm-text">
                                            <span class="pm-title">Ví MoMo</span>
                                            <span class="pm-subtitle">Quét QR bằng app MoMo để thanh toán</span>
                                        </span>
                                        <span class="pm-dot"></span>
                                    </label>

                                    {{-- VNPay --}}
                                    <input type="radio" class="pm-radio btn-check" name="payment_method" id="pm_vnpay" value="vnpay">
                                    <label class="pm-card" for="pm_vnpay">
                                        <span class="pm-icon pm-icon--brand" style="background:#005BAA;font-size:10px;line-height:1.1">VN<br>Pay</span>
                                        <span class="pm-text">
                                            <span class="pm-title">VNPAY</span>
                                            <span class="pm-subtitle">Thanh toán qua ví VNPAY hoặc QR ngân hàng</span>
                                        </span>
                                        <span class="pm-dot"></span>
                                    </label>

                                    {{-- Card --}}
                                    <input type="radio" class="pm-radio btn-check" name="payment_method" id="pm_card" value="card">
                                    <label class="pm-card" for="pm_card">
                                        <span class="pm-icon pm-icon--card"><i class="bi bi-credit-card-2-front"></i></span>
                                        <span class="pm-text">
                                            <span class="pm-title">Thẻ tín dụng / ghi nợ</span>
                                            <span class="pm-subtitle">Visa, Mastercard, JCB</span>
                                            <span class="pm-networks">
                                                <span class="pm-network-badge" style="background:#1a1f71">VISA</span>
                                                <span class="pm-network-badge" style="background:#eb001b">MASTERCARD</span>
                                                <span class="pm-network-badge" style="background:#0f6937">JCB</span>
                                            </span>
                                        </span>
                                        <span class="pm-dot"></span>
                                    </label>

                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-shield-check me-2"></i>Đặt hàng
                            </button>
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
        const proxyFields = document.getElementById('proxy-buyer-fields');
        const proxyInputs = proxyFields ? proxyFields.querySelectorAll('input[name="buyer_name"], input[name="buyer_phone"]') : [];

        function toggleProxyFields() {
            const isProxy = document.getElementById('buyer_type_proxy')?.checked;
            proxyFields?.classList.toggle('d-none', !isProxy);
            proxyInputs.forEach(input => input.required = !!isProxy);
        }

        document.querySelectorAll('input[name="buyer_type"]').forEach(radio => {
            radio.addEventListener('change', toggleProxyFields);
        });
        toggleProxyFields();

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
