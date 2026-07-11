@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('header')
    <h1 class="h2 mb-1 text-success"><i class="bi bi-check-circle"></i> Đặt hàng thành công</h1>
@endsection

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <p>Mã đơn hàng: <strong>{{ $order->order_code }}</strong></p>
            <p>Tổng tiền: <strong>{{ number_format($order->total_amount, 0, ',', '.') }} đ</strong></p>
            <p class="text-muted">Cảm ơn bạn đã mua hàng tại H-Phone Store.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('orders.show', $order) }}" class="btn btn-primary">Xem đơn hàng</a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
@endsection
