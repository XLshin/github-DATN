@extends('layouts.admin')

@section('title', 'Tra cứu vận đơn')
@section('page_icon', 'bi-search')
@section('page_eyebrow', 'Quản lý vận chuyển')
@section('page_title', 'Tra cứu vận đơn')
@section('page_subtitle', 'Tra cứu thông tin vận đơn theo mã vận đơn, mã đơn hàng hoặc số điện thoại khách hàng.')

@section('heading_actions')
<a href="{{ route('admin.shipments.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel mb-4">
    <div class="panel-header">
        <form method="GET" action="{{ route('admin.shipments.lookup') }}" class="row g-2 flex-grow-1">
            <div class="col-md-8">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Nhập mã vận đơn, mã đơn hoặc SĐT">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Tra cứu
                </button>

                <a href="{{ route('admin.shipments.lookup') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>
</section>

@if (request()->filled('keyword'))
@if ($shipment)
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Kết quả tra cứu</h5>
            <div class="text-muted small">
                Thông tin vận đơn tìm thấy trong hệ thống.
            </div>
        </div>

        <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-primary btn-sm">
            Xem chi tiết
        </a>
    </div>

    <div class="p-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Mã vận đơn</div>
                <div class="fw-semibold">
                    {{ $shipment->tracking_code ?? 'Chưa có' }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Đơn vị vận chuyển</div>
                <div class="fw-semibold">
                    {{ $shipment->shipping_unit }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Trạng thái giao hàng</div>

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

            <div class="col-md-4">
                <div class="text-muted small">Mã đơn</div>
                <div class="fw-semibold">
                    {{ $shipment->order->order_code ?? 'Không có' }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Khách hàng</div>
                <div class="fw-semibold">
                    {{ $shipment->order->customer_name ?? 'Không có' }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">SĐT</div>
                <div class="fw-semibold">
                    {{ $shipment->order->customer_phone ?? 'Không có' }}
                </div>
            </div>
        </div>
    </div>
</section>
@else
<section class="panel">
    <div class="p-4 text-center text-muted">
        Không tìm thấy vận đơn phù hợp với từ khóa:
        <strong>{{ request('keyword') }}</strong>
    </div>
</section>
@endif
@endif
@endsection