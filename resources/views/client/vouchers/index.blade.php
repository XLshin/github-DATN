@extends('layouts.client')

@section('title', 'Voucher của tôi')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Voucher của tôi</h1>

    @if($coupons->isEmpty())
        <div class="alert alert-info">Bạn hiện không có voucher nào.</div>
    @else
        <div class="row">
            @foreach($coupons as $coupon)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $coupon->code }} <small class="text-muted">@if($coupon->type === 'percentage') Giảm {{ $coupon->value }}% @else Giảm {{ number_format($coupon->value) }}đ @endif</small></h5>
                            <p class="card-text">Hạn: {{ $coupon->start_date?->format('d/m/Y') ?? '--' }} - {{ $coupon->end_date?->format('d/m/Y') ?? '--' }}</p>
                            <p class="card-text"><small class="text-muted">@if($coupon->min_order_amount) Áp dụng cho đơn từ {{ number_format($coupon->min_order_amount) }}đ @endif</small></p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
