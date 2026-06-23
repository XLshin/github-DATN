@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('header')
    <h1 class="h2 mb-1">Đơn hàng của tôi</h1>
@endsection

@section('content')
    @if ($orders->isEmpty())
        <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
    @else
        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $order->order_code }}</strong>
                            <div class="small text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="badge text-bg-primary">{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
@endsection
