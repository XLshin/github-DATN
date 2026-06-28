@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Chuyển đến cổng thanh toán (giả lập)</h2>

    <p>Order: {{ $order->order_code }} — Tổng {{ number_format($order->total_amount) }}</p>
    <p>Phương thức: {{ strtoupper($method) }}</p>

    <form method="post" action="{{ route('checkout.success', $order->id) }}">
        @csrf
        <button class="btn btn-success">Hoàn tất thanh toán (giả lập)</button>
    </form>
</div>
@endsection
