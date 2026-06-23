@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng')

@section('header')
    <h1 class="h2 mb-1">Đơn hàng {{ $order->order_code }}</h1>
    <p class="text-muted mb-0">{{ $order->created_at->format('d/m/Y H:i') }}</p>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Sản phẩm</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Tên</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Sản phẩm' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->total, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p><strong>Trạng thái:</strong> {{ $order->status }}</p>
                    <p><strong>Người nhận:</strong> {{ $order->customer_name }}</p>
                    <p><strong>SĐT:</strong> {{ $order->customer_phone }}</p>
                    <p><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                    @if($order->shipment)
                    <hr>
                    <h6 class="mb-2">Vận đơn</h6>
                    <p class="mb-1"><strong>Đơn vị:</strong> {{ $order->shipment->shipping_unit ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Mã vận đơn:</strong>
                        @if($order->shipment->tracking_code)
                            <a href="{{ $order->shipment->tracking_url ?? '#' }}" target="_blank">{{ $order->shipment->tracking_code }}</a>
                        @else
                            Chưa có
                        @endif
                    </p>
                    <p class="mb-0"><strong>Trạng thái giao:</strong> {{ $order->shipment->shipping_status ?? 'Chưa có' }}</p>
                    @endif
                    <hr>
                    <p class="fs-5 fw-bold text-primary mb-0">Tổng: {{ number_format($order->total_amount, 0, ',', '.') }} đ</p>
                </div>
            </div>
        </div>
    </div>
@endsection
