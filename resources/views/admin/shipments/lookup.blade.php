@extends('admin.layouts.app')

@section('content')
    <h1>Tra cứu vận đơn</h1>

    <form method="GET" action="{{ route('admin.shipments.lookup') }}">
        <input type="text" name="keyword" value="{{ request('keyword') }}"
               placeholder="Nhập mã vận đơn, mã đơn hoặc SĐT">
        <button type="submit">Tra cứu</button>
    </form>

    @if (request()->filled('keyword'))
        @if ($shipment)
            <h2>Kết quả</h2>

            <p><strong>Mã vận đơn:</strong> {{ $shipment->tracking_code ?? 'Chưa có' }}</p>
            <p><strong>Đơn vị vận chuyển:</strong> {{ $shipment->shipping_unit }}</p>
            <p><strong>Trạng thái giao hàng:</strong> {{ $shipment->shipping_status }}</p>
            <p><strong>Mã đơn:</strong> {{ $shipment->order->order_code ?? 'Không có' }}</p>
            <p><strong>Khách hàng:</strong> {{ $shipment->order->customer_name ?? 'Không có' }}</p>
            <p><strong>SĐT:</strong> {{ $shipment->order->customer_phone ?? 'Không có' }}</p>

            <a href="{{ route('admin.shipments.show', $shipment) }}">Xem chi tiết</a>
        @else
            <p>Không tìm thấy vận đơn.</p>
        @endif
    @endif
@endsection