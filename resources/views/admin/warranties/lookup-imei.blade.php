@extends('admin.layouts.app')

@section('content')
    <h1>Tra cứu bảo hành theo IMEI</h1>

    <form method="GET" action="{{ route('admin.warranties.lookupImei') }}">
        <input type="text" name="imei" value="{{ request('imei') }}" placeholder="Nhập IMEI">
        <button type="submit">Tra cứu</button>
    </form>

    @if (request()->filled('imei'))
        @if ($imei)
            <h2>Thông tin IMEI</h2>

            <p><strong>IMEI:</strong> {{ $imei->imei }}</p>
            <p><strong>Trạng thái IMEI:</strong> {{ $imei->status }}</p>

            <h2>Phiếu bảo hành hiện tại</h2>

            @if ($currentWarranty)
                <p><strong>Mã đơn:</strong> {{ $currentWarranty->order->order_code ?? 'Không có' }}</p>
                <p><strong>Khách hàng:</strong> {{ $currentWarranty->order->customer_name ?? 'Không có' }}</p>
                <p><strong>Ngày bắt đầu:</strong> {{ $currentWarranty->warranty_start }}</p>
                <p><strong>Ngày kết thúc:</strong> {{ $currentWarranty->warranty_end }}</p>
                <p><strong>Trạng thái:</strong> {{ $currentWarranty->status }}</p>

                <a href="{{ route('admin.warranties.show', $currentWarranty) }}">Xem chi tiết</a>
            @else
                <p>IMEI này hiện không có phiếu bảo hành active hoặc claimed.</p>
                <p>Có thể tạo phiếu bảo hành mới nếu cần.</p>

                <a href="{{ route('admin.warranties.create') }}">Tạo phiếu bảo hành</a>
            @endif

            <h2>Lịch sử phiếu bảo hành</h2>

            @if ($warranties->count())
                <table border="1" cellpadding="8">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo phiếu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($warranties as $warranty)
                            <tr>
                                <td>{{ $warranty->order->order_code ?? 'Không có' }}</td>
                                <td>{{ $warranty->warranty_start }}</td>
                                <td>{{ $warranty->warranty_end }}</td>
                                <td>{{ $warranty->status }}</td>
                                <td>{{ $warranty->created_at }}</td>
                                <td>
                                    <a href="{{ route('admin.warranties.show', $warranty) }}">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>IMEI này chưa từng có phiếu bảo hành.</p>
            @endif
        @else
            <p>Không tìm thấy IMEI.</p>
        @endif
    @endif
@endsection