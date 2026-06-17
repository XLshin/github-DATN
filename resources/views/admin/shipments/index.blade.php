@extends('admin.layouts.app')

@section('content')
    <h1>Quản lý vận chuyển</h1>

    <h2>Đơn hàng có thể tạo vận đơn</h2>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>SĐT</th>
            <th>Địa chỉ</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($ordersCanCreateShipment as $order)
            <tr>
                <td>{{ $order->order_code }}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->customer_phone }}</td>
                <td>{{ $order->shipping_address }}</td>
                <td>{{ $order->status }}</td>
                <td>
                    <a href="{{ route('admin.shipments.createFromOrder', $order) }}">
                        Tạo vận đơn
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Không có đơn hàng nào đang chờ tạo vận đơn.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<hr>
    
    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <form method="GET" action="{{ route('admin.shipments.index') }}">
        <input type="text" name="keyword" value="{{ request('keyword') }}"
               placeholder="Mã vận đơn, mã đơn, tên hoặc SĐT">

        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="pending" @selected(request('status') === 'pending')>Chờ giao</option>
            <option value="shipping" @selected(request('status') === 'shipping')>Đang giao</option>
            <option value="delivered" @selected(request('status') === 'delivered')>Đã giao</option>
            <option value="failed" @selected(request('status') === 'failed')>Giao thất bại</option>
        </select>

        <button type="submit">Tìm kiếm</button>
    </form>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Đơn vị vận chuyển</th>
                <th>Mã vận đơn</th>
                <th>Trạng thái giao hàng</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($shipments as $shipment)
                <tr>
                    <td>{{ $shipment->order->order_code ?? 'Không có' }}</td>
                    <td>{{ $shipment->order->customer_name ?? 'Không có' }}</td>
                    <td>{{ $shipment->shipping_unit }}</td>
                    <td>{{ $shipment->tracking_code ?? 'Chưa có' }}</td>
                    <td>{{ $shipment->shipping_status }}</td>
                    <td>{{ $shipment->created_at }}</td>
                    <td>
                        <a href="{{ route('admin.shipments.show', $shipment) }}">Chi tiết</a>

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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Chưa có vận đơn nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $shipments->links() }}
@endsection