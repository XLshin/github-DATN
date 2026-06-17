@extends('admin.layouts.app')

@section('content')
    <h1>Quản lý bảo hành</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('admin.warranties.create') }}">Tạo phiếu bảo hành</a>
    <a href="{{ route('admin.warranties.lookupImei') }}">Tra cứu IMEI</a>

    <form method="GET" action="{{ route('admin.warranties.index') }}">
        <input type="text" name="keyword" value="{{ request('keyword') }}"
               placeholder="IMEI, mã đơn, tên hoặc SĐT">

        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="active" @selected(request('status') === 'active')>Còn bảo hành</option>
            <option value="expired" @selected(request('status') === 'expired')>Hết hạn</option>
            <option value="claimed" @selected(request('status') === 'claimed')>Đang bảo hành</option>
        </select>

        <button type="submit">Tìm kiếm</button>
    </form>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>IMEI</th>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Bắt đầu</th>
                <th>Kết thúc</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($warranties as $warranty)
                <tr>
                    <td>{{ $warranty->imei->imei ?? 'Không có' }}</td>
                    <td>{{ $warranty->order->order_code ?? 'Không có' }}</td>
                    <td>{{ $warranty->order->customer_name ?? 'Không có' }}</td>
                    <td>{{ $warranty->warranty_start }}</td>
                    <td>{{ $warranty->warranty_end }}</td>
                    <td>{{ $warranty->status }}</td>
                    <td>
                        <a href="{{ route('admin.warranties.show', $warranty) }}">Chi tiết</a>
                        <a href="{{ route('admin.warranties.edit', $warranty) }}">Sửa</a>

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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Chưa có phiếu bảo hành nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $warranties->links() }}
@endsection