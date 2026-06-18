@extends('admin.layouts.app')

@section('content')
    <h1>Chi tiết vận đơn</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <h2>Thông tin vận chuyển</h2>

    <p><strong>Mã vận đơn:</strong> {{ $shipment->tracking_code ?? 'Chưa có' }}</p>
    <p><strong>Đơn vị vận chuyển:</strong> {{ $shipment->shipping_unit }}</p>
    <p><strong>Trạng thái:</strong> {{ $shipment->shipping_status }}</p>
    <p><strong>Ngày bắt đầu giao:</strong> {{ $shipment->shipped_at ?? 'Chưa có' }}</p>
    <p><strong>Ngày giao thành công:</strong> {{ $shipment->delivered_at ?? 'Chưa có' }}</p>

    <h2>Thông tin đơn hàng</h2>

    <p><strong>Mã đơn:</strong> {{ $shipment->order->order_code ?? 'Không có' }}</p>
    <p><strong>Khách hàng:</strong> {{ $shipment->order->customer_name ?? 'Không có' }}</p>
    <p><strong>SĐT:</strong> {{ $shipment->order->customer_phone ?? 'Không có' }}</p>
    <p><strong>Địa chỉ:</strong> {{ $shipment->order->shipping_address ?? 'Không có' }}</p>
    <p><strong>Trạng thái đơn:</strong> {{ $shipment->order->status ?? 'Không có' }}</p>

    <h2>Cập nhật trạng thái giao hàng</h2>

    <form method="POST" action="{{ route('admin.shipments.updateStatus', $shipment) }}">
        @csrf
        @method('PATCH')

        <select name="shipping_status">
            <option value="pending" @selected($shipment->shipping_status === 'pending')>Chờ giao</option>
            <option value="shipping" @selected($shipment->shipping_status === 'shipping')>Đang giao</option>
            <option value="delivered" @selected($shipment->shipping_status === 'delivered')>Đã giao</option>
            <option value="failed" @selected($shipment->shipping_status === 'failed')>Giao thất bại</option>
        </select>

        <button type="submit">Cập nhật</button>
    </form>

    <h2>Lịch sử vận chuyển</h2>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Thời gian</th>
                <th>Sự kiện</th>
                <th>Mô tả</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($histories as $history)
                <tr>
                    <td>{{ $history['time'] }}</td>
                    <td>{{ $history['title'] }}</td>
                    <td>{{ $history['description'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <a href="{{ route('admin.shipments.index') }}">Quay lại danh sách</a>
@endsection