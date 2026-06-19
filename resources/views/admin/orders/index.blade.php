@extends('layouts.admin')

@section('title', 'Đơn hàng')
@section('page_icon', 'bi-receipt')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Danh sách đơn hàng')
@section('page_subtitle', 'Quản lý và xem chi tiết các đơn hàng trong hệ thống.')

@section('heading_actions')
<a href="{{ url('/') }}" class="btn btn-light btn-sm">
    <i class="bi bi-house"></i> Về trang chủ
</a>
@endsection

@section('content')
<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th class="text-end">Tổng tiền</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="fw-semibold">
                        {{ $order->order_code }}
                    </td>

                    <td>
                        {{ $order->user->name ?? 'Guest' }}
                    </td>

                    <td class="text-end fw-semibold">
                        {{ number_format($order->total_amount, 0, ',', '.') }} đ
                    </td>

                    <td class="text-end">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light btn-sm">
                            Xem chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Không có đơn hàng nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="p-3">
        {{ $orders->links() }}
    </div>
    @endif
</section>
@endsection