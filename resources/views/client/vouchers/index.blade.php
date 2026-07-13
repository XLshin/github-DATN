@extends('layouts.app')

@section('title', 'Voucher của tôi')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Voucher của tôi</h1>

    @if($ownedCoupons->isEmpty() && $availablePublicCoupons->isEmpty())
        <div class="alert alert-info">Bạn hiện không có voucher nào.</div>
    @endif

    @if($ownedCoupons->isNotEmpty())
        <h3 class="mb-3">Voucher của bạn</h3>
        <div class="row mb-4">
            @foreach($ownedCoupons as $coupon)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $coupon->code }} <small class="text-muted">@if($coupon->discount_type === 'percent') Giảm {{ $coupon->discount_value }}% @else Giảm {{ number_format($coupon->discount_value, 0, ',', '.') }}đ @endif</small></h5>
                            <p class="card-text">Hạn: {{ $coupon->start_date?->format('d/m/Y') ?? '--' }} - {{ $coupon->end_date?->format('d/m/Y') ?? '--' }}</p>
                            <p class="card-text"><small class="text-muted">@if($coupon->min_order_amount) Áp dụng cho đơn từ {{ number_format($coupon->min_order_amount, 0, ',', '.') }}đ @endif</small></p>
                            <span class="badge bg-primary">{{ $coupon->isPublic() ? 'Đã nhận' : 'Gán riêng' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($availablePublicCoupons->isNotEmpty())
        <h3 class="mb-3">Voucher công khai có thể nhận</h3>
        <div class="row">
            @foreach($availablePublicCoupons as $coupon)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $coupon->code }} <small class="text-muted">@if($coupon->discount_type === 'percent') Giảm {{ $coupon->discount_value }}% @else Giảm {{ number_format($coupon->discount_value, 0, ',', '.') }}đ @endif</small></h5>
                            <p class="card-text">Hạn: {{ $coupon->start_date?->format('d/m/Y') ?? '--' }} - {{ $coupon->end_date?->format('d/m/Y') ?? '--' }}</p>
                            <p class="card-text"><small class="text-muted">@if($coupon->min_order_amount) Áp dụng cho đơn từ {{ number_format($coupon->min_order_amount, 0, ',', '.') }}đ @endif</small></p>
                            <form action="{{ route('client.vouchers.claim', $coupon) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm">Nhận voucher</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($ownedCoupons->isNotEmpty())
        <div class="alert alert-secondary">Không có voucher công khai mới để nhận.</div>
    @endif
</div>
@endsection
