@extends('layouts.app')

@section('title', 'Thanh toán')

@section('header')
    <h1 class="h2 mb-1">Thanh toán</h1>
    <p class="text-muted mb-0">Hoàn tất thông tin giao hàng</p>
@endsection

@push('styles')
<style>
    .checkout-section {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .checkout-section__title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 18px;
    }

    .checkout-section__badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #eef5ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .buyer-type-toggle { display: flex; gap: 10px; }
    .buyer-type-option { flex: 1; }
    .buyer-type-option input { position: absolute; opacity: 0; pointer-events: none; }
    .buyer-type-option label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid #e3e6ea;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        color: #495057;
        transition: border-color .15s ease, background-color .15s ease;
        margin: 0;
    }
    .buyer-type-option input:checked + label {
        border-color: #0d6efd;
        background: #eef5ff;
        color: #0d6efd;
    }

    .address-card-list { display: flex; flex-direction: column; gap: 10px; }
    .address-card { position: relative; }
    .address-card input { position: absolute; opacity: 0; pointer-events: none; }
    .address-card label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid #e3e6ea;
        border-radius: 12px;
        cursor: pointer;
        margin: 0;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .address-card label:hover { border-color: #9fc2ff; background: #f8faff; }
    .address-card input:checked + label {
        border-color: #0d6efd;
        background: #eef5ff;
        box-shadow: 0 0 0 1px #0d6efd inset;
    }
    .address-card-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #ced4da;
        flex-shrink: 0;
        margin-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .address-card-dot::after {
        content: '';
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #0d6efd;
        transform: scale(0);
        transition: transform .15s ease;
    }
    .address-card input:checked + label .address-card-dot { border-color: #0d6efd; }
    .address-card input:checked + label .address-card-dot::after { transform: scale(1); }
    .address-card-body { flex: 1 1 auto; min-width: 0; }
    .address-card-name { font-weight: 600; }
    .address-card-label {
        font-size: .6875rem;
        font-weight: 700;
        color: #0d6efd;
        background: #eef5ff;
        border-radius: 6px;
        padding: 1px 8px;
        margin-left: 6px;
        vertical-align: middle;
    }
    .address-card-default {
        font-size: .6875rem;
        font-weight: 700;
        color: #1a9a6c;
        background: #e3f3ec;
        border-radius: 6px;
        padding: 1px 8px;
        margin-left: 6px;
        vertical-align: middle;
    }
    .address-card-text { color: #6c757d; font-size: .875rem; margin-top: 2px; }

    .add-address-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px;
        border: 1.5px dashed #ced4da;
        border-radius: 12px;
        color: #495057;
        font-weight: 600;
        background: #fff;
        cursor: pointer;
    }
    .add-address-toggle:hover { border-color: #0d6efd; color: #0d6efd; background: #f8faff; }

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

    .checkout-summary {
        position: sticky;
        top: 20px;
    }

    .checkout-summary-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .checkout-summary-item:last-child { border-bottom: none; }
    .checkout-summary-thumb {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        background: #f1f3f5;
    }
    .checkout-summary-name { font-weight: 600; font-size: .9rem; }
    .checkout-summary-meta { color: #6c757d; font-size: .8125rem; }
</style>
@endpush

@section('content')
    @if ($items->isEmpty())
        <div class="alert alert-warning">Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></div>
    @else
        <div id="checkout-preview-error" class="alert alert-danger d-none">
            <div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <form method="POST" action="{{ route('checkout.process') }}" class="needs-validation" novalidate>
                    @csrf

                    @foreach($selectedIds ?? [] as $selectedId)
                        <input type="hidden" name="cart_item_ids[]" value="{{ $selectedId }}">
                    @endforeach

                    {{-- Người mua hàng --}}
                    <div class="checkout-section">
                        <div class="checkout-section__title">
                            <span class="checkout-section__badge"><i class="bi bi-person"></i></span>
                            Người mua hàng
                        </div>

                        <div class="buyer-type-toggle mb-3">
                            <div class="buyer-type-option">
                                <input type="radio" name="buyer_type" id="buyer_type_self" value="self"
                                       {{ old('buyer_type', 'self') === 'self' ? 'checked' : '' }}>
                                <label for="buyer_type_self"><i class="bi bi-person-check"></i> Chính tôi</label>
                            </div>
                            <div class="buyer-type-option">
                                <input type="radio" name="buyer_type" id="buyer_type_proxy" value="proxy"
                                       {{ old('buyer_type') === 'proxy' ? 'checked' : '' }}>
                                <label for="buyer_type_proxy"><i class="bi bi-people"></i> Mua hộ người khác</label>
                            </div>
                        </div>

                        @php
                            $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
                            $buyerDefaultName = $defaultAddress->name ?? (auth()->user()->name ?? '');
                            $buyerDefaultPhone = $defaultAddress->phone ?? (auth()->user()->phone ?? '');
                        @endphp
                        <div id="proxy-buyer-fields" class="{{ old('buyer_type') === 'proxy' ? '' : 'd-none' }} border rounded p-3 bg-light">
                            <div class="text-muted small mb-2">
                                <i class="bi bi-info-circle"></i>
                                Tự động lấy theo thông tin tài khoản của bạn, có thể chỉnh sửa.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên người đặt mua <span class="text-danger">*</span></label>
                                    <input type="text" id="buyer_name" name="buyer_name" class="form-control" value="{{ old('buyer_name', $buyerDefaultName) }}" required>
                                    @error('buyer_name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SĐT người đặt mua <span class="text-danger">*</span></label>
                                    <input type="text" id="buyer_phone" name="buyer_phone" class="form-control" value="{{ old('buyer_phone', $buyerDefaultPhone) }}" required>
                                    @error('buyer_phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-muted small mt-3 mb-0">
                                <i class="bi bi-info-circle"></i>
                                Thông tin bên dưới là <strong>người nhận hàng</strong> — có thể khác với bạn.
                            </div>
                        </div>
                    </div>

                    {{-- Địa chỉ đã lưu (chỉ khi tự mua) --}}
                    <div class="checkout-section" id="saved-address-section">
                        <div class="checkout-section__title">
                            <span class="checkout-section__badge"><i class="bi bi-geo-alt"></i></span>
                            Địa chỉ giao hàng
                        </div>

                        @if($addresses->isNotEmpty())
                            <div class="address-card-list mb-3" id="address-card-list">
                                @foreach($addresses as $address)
                                    <div class="address-card">
                                        <input type="radio"
                                               name="saved_address_id"
                                               id="address_{{ $address->id }}"
                                               value="{{ $address->id }}"
                                               data-name="{{ $address->name }}"
                                               data-phone="{{ $address->phone }}"
                                               data-full-address="{{ collect([$address->address_line, $address->ward, $address->district, $address->city])->filter()->implode(', ') }}"
                                               {{ (old('saved_address_id') ? old('saved_address_id') == $address->id : $address->is_default) ? 'checked' : '' }}>
                                        <label for="address_{{ $address->id }}">
                                            <span class="address-card-dot"></span>
                                            <span class="address-card-body">
                                                <span class="address-card-name">
                                                    {{ $address->name }} · {{ $address->phone }}
                                                    @if($address->label)
                                                        <span class="address-card-label">{{ $address->label }}</span>
                                                    @endif
                                                    @if($address->is_default)
                                                        <span class="address-card-default">Mặc định</span>
                                                    @endif
                                                </span>
                                                <span class="address-card-text">
                                                    {{ collect([$address->address_line, $address->ward, $address->district, $address->city])->filter()->implode(', ') }}
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <button type="button" class="add-address-toggle" id="add-address-toggle">
                            <i class="bi bi-plus-circle"></i> Thêm địa chỉ mới
                        </button>

                        <div id="new-address-form" class="d-none border rounded p-3 mt-3 bg-light">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nhãn địa chỉ</label>
                                    <input type="text" id="new_address_label" class="form-control" placeholder="Nhà riêng, Công ty...">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4 pt-1">
                                        <input class="form-check-input" type="checkbox" id="new_address_default">
                                        <label class="form-check-label" for="new_address_default">Đặt làm mặc định</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Họ tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" id="new_address_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" id="new_address_phone" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                    <input type="text" id="new_address_line" class="form-control" placeholder="Số nhà, tên đường" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phường/Xã</label>
                                    <input type="text" id="new_address_ward" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quận/Huyện</label>
                                    <input type="text" id="new_address_district" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tỉnh/Thành phố</label>
                                    <input type="text" id="new_address_city" class="form-control">
                                </div>
                            </div>
                            <div id="new-address-error" class="text-danger small mt-2 d-none"></div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-primary btn-sm" id="new-address-save">
                                    <i class="bi bi-check2"></i> Lưu địa chỉ
                                </button>
                                <button type="button" class="btn btn-light btn-sm" id="new-address-cancel">Hủy</button>
                            </div>
                        </div>
                    </div>

                    {{-- Thông tin người nhận --}}
                    <div class="checkout-section" id="receiver-section">
                        <div class="checkout-section__title">
                            <span class="checkout-section__badge"><i class="bi bi-box-seam"></i></span>
                            Thông tin người nhận
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ tên người nhận <span class="text-danger">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required>
                                @error('customer_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" id="customer_phone" name="customer_phone" class="form-control" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required>
                                @error('customer_phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" required>{{ old('shipping_address', auth()->user()->address ?? '') }}</textarea>
                                @error('shipping_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Voucher & điểm --}}
                    <div class="checkout-section">
                        <div class="checkout-section__title">
                            <span class="checkout-section__badge"><i class="bi bi-ticket-perforated"></i></span>
                            Ưu đãi
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
                        <div class="mb-0">
                            <label class="form-label">Dùng điểm</label>
                            <div class="input-group">
                                <input type="number" name="points_to_use" class="form-control" value="{{ old('points_to_use', 0) }}" min="0" max="{{ auth()->user()->points ?? 0 }}">
                                <span class="input-group-text">đ (Bạn có: {{ number_format(auth()->user()->points ?? 0) }} điểm)</span>
                            </div>
                            @error('points_to_use')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Phương thức thanh toán --}}
                    <div class="checkout-section">
                        <div class="checkout-section__title">
                            <span class="checkout-section__badge"><i class="bi bi-wallet2"></i></span>
                            Phương thức thanh toán
                        </div>

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
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 checkout-summary">
                    <div class="card-header bg-white fw-bold">Đơn hàng</div>
                    <ul class="list-group list-group-flush">
                        @foreach ($items as $item)
                            @php
                                $variant = $item->productVariant;
                                $unitPrice = (float) ($item->product?->price ?? 0) + (float) ($variant?->additional_price ?? 0);
                                $lineTotal = $unitPrice * (int) $item->quantity;
                                $thumb = $variant?->image_path ?? $item->product?->thumbnail;
                            @endphp
                            <li class="list-group-item checkout-summary-item">
                                @if($thumb)
                                    <img class="checkout-summary-thumb" src="{{ \Illuminate\Support\Facades\Storage::url($thumb) }}" alt="">
                                @else
                                    <span class="checkout-summary-thumb"></span>
                                @endif
                                <div class="flex-grow-1">
                                    <div class="checkout-summary-name">{{ $item->product->name }} x{{ $item->quantity }}</div>
                                    @if($variant)
                                        <div class="checkout-summary-meta">{{ $variant->color ?: 'Không màu' }}</div>
                                    @endif
                                </div>
                                <div class="fw-semibold text-nowrap">{{ number_format($lineTotal, 0, ',', '.') }} đ</div>
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
        const savedAddressSection = document.getElementById('saved-address-section');
        const receiverNameInput = document.getElementById('customer_name');
        const receiverPhoneInput = document.getElementById('customer_phone');
        const receiverAddressInput = document.getElementById('shipping_address');

        const accountName = @json($buyerDefaultName ?? (auth()->user()->name ?? ''));
        const accountPhone = @json($buyerDefaultPhone ?? (auth()->user()->phone ?? ''));
        const accountAddress = @json(auth()->user()->address ?? '');

        const buyerNameInput = document.getElementById('buyer_name');
        const buyerPhoneInput = document.getElementById('buyer_phone');

        function applySelectedAddress() {
            const checked = document.querySelector('input[name="saved_address_id"]:checked');
            if (!checked) return;
            receiverNameInput.value = checked.dataset.name || '';
            receiverPhoneInput.value = checked.dataset.phone || '';
            receiverAddressInput.value = checked.dataset.fullAddress || '';
        }

        document.querySelectorAll('input[name="saved_address_id"]').forEach(radio => {
            radio.addEventListener('change', applySelectedAddress);
        });

        // Toggle: đặt hộ ẩn địa chỉ đã lưu của chủ tài khoản, yêu cầu nhập tay thông tin người nhận.
        function toggleBuyerType() {
            const isProxy = document.getElementById('buyer_type_proxy')?.checked;
            const proxyFields = document.getElementById('proxy-buyer-fields');
            const proxyInputs = proxyFields ? proxyFields.querySelectorAll('input[name="buyer_name"], input[name="buyer_phone"]') : [];

            proxyFields?.classList.toggle('d-none', !isProxy);
            proxyInputs.forEach(input => input.required = !!isProxy);

            if (isProxy) {
                // Tự động lấy theo tài khoản đang đăng nhập (nếu người dùng chưa từng chỉnh sửa), vẫn có thể sửa lại.
                if (buyerNameInput && !buyerNameInput.value.trim()) buyerNameInput.value = accountName;
                if (buyerPhoneInput && !buyerPhoneInput.value.trim()) buyerPhoneInput.value = accountPhone;

                savedAddressSection.classList.add('d-none');
                document.querySelectorAll('input[name="saved_address_id"]').forEach(r => r.checked = false);
                receiverNameInput.value = '';
                receiverPhoneInput.value = '';
                receiverAddressInput.value = '';
            } else {
                savedAddressSection.classList.remove('d-none');
                const defaultRadio = document.querySelector('input[name="saved_address_id"]:checked')
                    || document.querySelector('input[name="saved_address_id"]');
                if (defaultRadio) {
                    defaultRadio.checked = true;
                    applySelectedAddress();
                } else {
                    receiverNameInput.value = accountName;
                    receiverPhoneInput.value = accountPhone;
                    receiverAddressInput.value = accountAddress;
                }
            }
        }

        document.querySelectorAll('input[name="buyer_type"]').forEach(radio => {
            radio.addEventListener('change', toggleBuyerType);
        });

        toggleBuyerType();
        if (!document.getElementById('buyer_type_proxy')?.checked) {
            applySelectedAddress();
        }

        // Thêm địa chỉ mới
        const addToggleBtn = document.getElementById('add-address-toggle');
        const newAddressForm = document.getElementById('new-address-form');
        const newAddressError = document.getElementById('new-address-error');
        const addressCardList = document.getElementById('address-card-list');

        addToggleBtn?.addEventListener('click', () => {
            newAddressForm.classList.remove('d-none');
            addToggleBtn.classList.add('d-none');
        });

        document.getElementById('new-address-cancel')?.addEventListener('click', () => {
            newAddressForm.classList.add('d-none');
            addToggleBtn.classList.remove('d-none');
            newAddressError.classList.add('d-none');
        });

        document.getElementById('new-address-save')?.addEventListener('click', () => {
            const requiredFieldIds = ['new_address_name', 'new_address_phone', 'new_address_line'];
            const firstInvalid = requiredFieldIds
                .map(id => document.getElementById(id))
                .find(input => !input.value.trim());

            if (firstInvalid) {
                newAddressError.textContent = 'Vui lòng nhập đầy đủ các trường bắt buộc (*).';
                newAddressError.classList.remove('d-none');
                firstInvalid.focus();
                return;
            }

            const payload = {
                label: document.getElementById('new_address_label').value,
                name: document.getElementById('new_address_name').value,
                phone: document.getElementById('new_address_phone').value,
                address_line: document.getElementById('new_address_line').value,
                ward: document.getElementById('new_address_ward').value,
                district: document.getElementById('new_address_district').value,
                city: document.getElementById('new_address_city').value,
                is_default: document.getElementById('new_address_default').checked,
            };

            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : null;

            fetch('{{ route('addresses.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            }).then(async r => {
                const data = await r.json().catch(() => ({}));

                if (!r.ok) {
                    const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                    newAddressError.textContent = firstError || 'Không thể lưu địa chỉ. Vui lòng kiểm tra lại thông tin.';
                    newAddressError.classList.remove('d-none');
                    return;
                }

                newAddressError.classList.add('d-none');
                newAddressForm.classList.add('d-none');
                addToggleBtn.classList.remove('d-none');
                ['label', 'name', 'phone', 'address_line', 'ward', 'district', 'city'].forEach(field => {
                    document.getElementById('new_address_' + field).value = '';
                });
                document.getElementById('new_address_default').checked = false;

                // Tải lại trang để lấy đúng id địa chỉ vừa tạo và danh sách cập nhật từ server.
                window.location.reload();
            }).catch(() => {
                newAddressError.textContent = 'Không thể lưu địa chỉ. Vui lòng thử lại.';
                newAddressError.classList.remove('d-none');
            });
        });

        // Preview voucher / điểm
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
