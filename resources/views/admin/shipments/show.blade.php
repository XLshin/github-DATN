@extends('layouts.admin')

@section('title', 'Chi tiết vận đơn')
@section('page_icon', 'bi-truck')
@section('page_eyebrow', 'Quản lý vận chuyển')
@section('page_title', 'Chi tiết vận đơn')
@section('page_subtitle', 'Xem thông tin vận chuyển, đơn hàng, trạng thái giao hàng và lịch sử vận chuyển.')

@section('heading_actions')
<a href="{{ route('admin.shipments.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
</a>
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin vận chuyển</h5>
                    <div class="text-muted small">
                        Thông tin mã vận đơn, đơn vị vận chuyển và tiến trình giao hàng.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã vận đơn</div>
                    <div class="fw-semibold">
                        {{ $shipment->tracking_code ?? 'Chưa có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Đơn vị vận chuyển</div>
                    <div class="fw-semibold">
                        {{ $shipment->shipping_unit }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Trạng thái</div>

                    @if($shipment->shipping_status === 'pending')
                    <span class="badge text-bg-secondary">Chờ giao</span>
                    @elseif($shipment->shipping_status === 'shipping')
                    <span class="badge text-bg-primary">Đang giao</span>
                    @elseif($shipment->shipping_status === 'delivered')
                    <span class="badge text-bg-success">Đã giao</span>
                    @elseif($shipment->shipping_status === 'failed')
                    <span class="badge text-bg-danger">Giao thất bại</span>
                    @else
                    <span class="badge text-bg-light">
                        {{ $shipment->shipping_status }}
                    </span>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Ngày bắt đầu giao</div>
                    <div class="fw-semibold">
                        {{ $shipment->shipped_at ?? 'Chưa có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Ngày giao thành công</div>
                    <div class="fw-semibold">
                        {{ $shipment->delivered_at ?? 'Chưa có' }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin đơn hàng</h5>
                    <div class="text-muted small">
                        Thông tin khách hàng và địa chỉ giao hàng.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã đơn</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->order_code ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->customer_name ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">SĐT</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->customer_phone ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Địa chỉ</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->shipping_address ?? 'Không có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Trạng thái đơn</div>
                    <span class="badge text-bg-secondary">
                        {{ $shipment->order->status ?? 'Không có' }}
                    </span>
                </div>
            </div>
        </section>
    </div>
</div>

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Cập nhật trạng thái giao hàng</h5>
            <div class="text-muted small">
                Thay đổi trạng thái hiện tại của vận đơn.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form method="POST" action="{{ route('admin.shipments.updateStatus', $shipment) }}" class="row g-2 align-items-end">
            @csrf
            @method('PATCH')

            <div class="col-md-5">
                <label class="form-label">
                    Trạng thái giao hàng
                </label>

                <select name="shipping_status" class="form-select form-select-sm">
                    <option value="pending" @selected($shipment->shipping_status === 'pending')>
                        Chờ giao
                    </option>

                    <option value="shipping" @selected($shipment->shipping_status === 'shipping')>
                        Đang giao
                    </option>

                    <option value="delivered" @selected($shipment->shipping_status === 'delivered')>
                        Đã giao
                    </option>

                    <option value="failed" @selected($shipment->shipping_status === 'failed')>
                        Giao thất bại
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Cập nhật
                </button>
            </div>
        </form>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Lịch sử vận chuyển</h5>
            <div class="text-muted small">
                Các mốc thời gian và sự kiện trong quá trình vận chuyển.
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Sự kiện</th>
                    <th>Mô tả</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($histories as $history)
                <tr>
                    <td class="fw-semibold">
                        {{ $history['time'] }}
                    </td>

                    <td>
                        {{ $history['title'] }}
                    </td>

                    <td>
                        {{ $history['description'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        Chưa có lịch sử vận chuyển.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection