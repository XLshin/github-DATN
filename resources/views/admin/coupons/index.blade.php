@extends('layouts.admin')

@section('title', 'Voucher')
@section('page_icon', 'bi-ticket-perforated')
@section('page_eyebrow', 'Khuyến mại')
@section('page_title', 'Quản lý voucher')
@section('page_subtitle', 'Quản lý mã voucher, thời hạn và hạn mức áp dụng cho khách hàng.')

@section('heading_actions')
    <a href="{{ route('coupons.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tạo voucher
    </a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <form method="GET" action="{{ route('coupons.index') }}" class="d-flex gap-2 flex-grow-1">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Tìm theo mã hoặc loại">
                <button type="submit" class="btn btn-outline-primary btn-sm">Tìm</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Mã voucher</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Đơn tối thiểu</th>
                        <th>Hạn mức</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->code }}</td>
                            <td>{{ $coupon->discount_type === 'percent' ? 'Phần trăm' : 'Cố định' }}</td>
                            <td>
                                @if ($coupon->discount_type === 'percent')
                                    {{ number_format($coupon->discount_value, 0, ',', '.') }}%
                                @else
                                    {{ number_format($coupon->discount_value, 0, ',', '.') }} đ
                                @endif
                            </td>
                            <td>{{ number_format($coupon->min_order_amount, 0, ',', '.') }} đ</td>
                            <td>{{ $coupon->usage_limit === 0 ? 'Không giới hạn' : $coupon->usage_limit . ' lần' }}</td>
                            <td>{{ $coupon->start_date->format('d/m/Y') }} - {{ $coupon->end_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $coupon->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $coupon->status ? 'Kích hoạt' : 'Tắt' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('coupons.assign-users-edit', $coupon) }}" class="btn btn-info btn-sm">Gán user</a>
                                    <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-light btn-sm">Sửa</a>
                                    <form action="{{ route('coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Xóa voucher này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có voucher nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($coupons->hasPages())
            <div class="p-3">{{ $coupons->withQueryString()->links() }}</div>
        @endif
    </section>
@endsection
