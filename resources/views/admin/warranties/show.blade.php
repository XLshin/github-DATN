@extends('admin.layouts.app')

@section('content')
    <h1>Chi tiết bảo hành</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <h2>Thông tin phiếu</h2>

    <p><strong>IMEI:</strong> {{ $warranty->imei->imei ?? 'Không có' }}</p>
    <p><strong>Mã đơn:</strong> {{ $warranty->order->order_code ?? 'Không có' }}</p>
    <p><strong>Khách hàng:</strong> {{ $warranty->order->customer_name ?? 'Không có' }}</p>
    <p><strong>SĐT:</strong> {{ $warranty->order->customer_phone ?? 'Không có' }}</p>
    <p><strong>Ngày bắt đầu:</strong> {{ $warranty->warranty_start }}</p>
    <p><strong>Ngày kết thúc:</strong> {{ $warranty->warranty_end }}</p>
    <p><strong>Trạng thái:</strong> {{ $warranty->status }}</p>

    <h2>Cập nhật trạng thái</h2>

    <form method="POST" action="{{ route('admin.warranties.updateStatus', $warranty) }}">
        @csrf
        @method('PATCH')

        <select name="status">
            <option value="active" @selected($warranty->status === 'active')>Còn bảo hành</option>
            <option value="expired" @selected($warranty->status === 'expired')>Hết hạn</option>
            <option value="claimed" @selected($warranty->status === 'claimed')>Đang bảo hành</option>
        </select>

        <button type="submit">Cập nhật</button>
    </form>

    <h2>Lịch sử bảo hành</h2>

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

    <a href="{{ route('admin.warranties.index') }}">Quay lại danh sách</a>
@endsection