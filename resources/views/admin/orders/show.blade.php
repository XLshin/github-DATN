@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')
@section('page_icon', 'bi-receipt')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Chi tiết đơn hàng')
@section('page_subtitle', 'Xem thông tin khách hàng, trạng thái và sản phẩm trong đơn hàng.')

@section('heading_actions')
<a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại đơn hàng
</a>
@endsection

@section('content')
<section class="panel mb-4">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">
                Đơn hàng {{ $order->order_code }}
            </h5>
            <div class="text-muted small">
                Thông tin tổng quan của đơn hàng.
            </div>
        </div>
    </div>

    <div class="p-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Mã đơn hàng</div>
                <div class="fw-semibold">
                    {{ $order->order_code }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Khách hàng</div>
                <div class="fw-semibold">
                    {{ $order->user->name ?? 'Guest' }}
                </div>
                <div class="text-muted small">
                    {{ $order->user->email ?? '-' }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Trạng thái</div>

                @if($order->status === 'pending')
                <span class="badge text-bg-secondary">Chờ xử lý</span>
                @elseif($order->status === 'processing')
                <span class="badge text-bg-primary">Đang xử lý</span>
                @elseif($order->status === 'completed')
                <span class="badge text-bg-success">Hoàn thành</span>
                @elseif($order->status === 'cancelled')
                <span class="badge text-bg-danger">Đã hủy</span>
                @else
                <span class="badge text-bg-light">
                    {{ $order->status }}
                </span>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Sản phẩm trong đơn</h5>
            <div class="text-muted small">
                Danh sách sản phẩm, số lượng và thành tiền.
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-end">Số lượng</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                @forelse($order->items as $item)
                <tr>
                    <td class="fw-semibold">
                        {{ $item->product->name ?? ('Product #' . $item->product_id) }}
                    </td>

                    <td class="text-end">
                        {{ $item->quantity }}
                    </td>

                    <td class="text-end fw-semibold">
                        {{ number_format($item->total, 0, ',', '.') }} đ
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        Đơn hàng chưa có sản phẩm.
                    </td>
                </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Tổng tiền</th>
                    <th class="text-end">
                        {{ number_format($order->total_amount, 0, ',', '.') }} đ
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>
@endsection