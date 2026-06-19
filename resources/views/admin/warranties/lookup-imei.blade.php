@extends('layouts.admin')

@section('title', 'Tra cứu bảo hành')
@section('page_icon', 'bi-search')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Tra cứu bảo hành theo IMEI')
@section('page_subtitle', 'Kiểm tra thông tin IMEI, phiếu bảo hành hiện tại và lịch sử bảo hành.')

@section('heading_actions')
<a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>

<a href="{{ route('admin.warranties.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Tạo phiếu bảo hành
</a>
@endsection

@section('content')
<section class="panel mb-4">
    <div class="panel-header">
        <form method="GET" action="{{ route('admin.warranties.lookupImei') }}" class="row g-2 flex-grow-1">
            <div class="col-md-8">
                <input
                    type="text"
                    name="imei"
                    value="{{ request('imei') }}"
                    class="form-control form-control-sm"
                    placeholder="Nhập IMEI">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Tra cứu
                </button>

                <a href="{{ route('admin.warranties.lookupImei') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>
</section>

@if (request()->filled('imei'))
@if ($imei)
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin IMEI</h5>
                    <div class="text-muted small">
                        Thông tin thiết bị được tìm thấy trong hệ thống.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">IMEI</div>
                    <div class="fw-semibold">
                        {{ $imei->imei }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Trạng thái IMEI</div>

                    @if($imei->status === 'available')
                    <span class="badge text-bg-success">Còn hàng</span>
                    @elseif($imei->status === 'sold')
                    <span class="badge text-bg-primary">Đã bán</span>
                    @elseif($imei->status === 'warranty')
                    <span class="badge text-bg-warning">Bảo hành</span>
                    @else
                    <span class="badge text-bg-secondary">
                        {{ $imei->status }}
                    </span>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Phiếu bảo hành hiện tại</h5>
                    <div class="text-muted small">
                        Phiếu bảo hành đang active hoặc đang xử lý.
                    </div>
                </div>
            </div>

            <div class="p-3">
                @if ($currentWarranty)
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Mã đơn</div>
                        <div class="fw-semibold">
                            {{ $currentWarranty->order->order_code ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Khách hàng</div>
                        <div class="fw-semibold">
                            {{ $currentWarranty->order->customer_name ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Ngày bắt đầu</div>
                        <div class="fw-semibold">
                            {{ $currentWarranty->warranty_start }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Ngày kết thúc</div>
                        <div class="fw-semibold">
                            {{ $currentWarranty->warranty_end }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái</div>

                        @if($currentWarranty->status === 'active')
                        <span class="badge text-bg-success">Còn bảo hành</span>
                        @elseif($currentWarranty->status === 'claimed')
                        <span class="badge text-bg-warning">Đang bảo hành</span>
                        @elseif($currentWarranty->status === 'expired')
                        <span class="badge text-bg-secondary">Hết hạn</span>
                        @else
                        <span class="badge text-bg-light">
                            {{ $currentWarranty->status }}
                        </span>
                        @endif
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <a href="{{ route('admin.warranties.show', $currentWarranty) }}" class="btn btn-primary btn-sm">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
                @else
                <div class="text-muted mb-3">
                    IMEI này hiện không có phiếu bảo hành active hoặc claimed.
                </div>

                <a href="{{ route('admin.warranties.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Tạo phiếu bảo hành
                </a>
                @endif
            </div>
        </section>
    </div>
</div>

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Lịch sử phiếu bảo hành</h5>
            <div class="text-muted small">
                Toàn bộ phiếu bảo hành từng được tạo cho IMEI này.
            </div>
        </div>
    </div>

    @if ($warranties->count())
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo phiếu</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($warranties as $warranty)
                <tr>
                    <td class="fw-semibold">
                        {{ $warranty->order->order_code ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $warranty->warranty_start }}
                    </td>

                    <td>
                        {{ $warranty->warranty_end }}
                    </td>

                    <td>
                        @if($warranty->status === 'active')
                        <span class="badge text-bg-success">Còn bảo hành</span>
                        @elseif($warranty->status === 'claimed')
                        <span class="badge text-bg-warning">Đang bảo hành</span>
                        @elseif($warranty->status === 'expired')
                        <span class="badge text-bg-secondary">Hết hạn</span>
                        @else
                        <span class="badge text-bg-light">
                            {{ $warranty->status }}
                        </span>
                        @endif
                    </td>

                    <td>
                        {{ $warranty->created_at }}
                    </td>

                    <td class="text-end">
                        <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
                            Chi tiết
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-4 text-center text-muted">
        IMEI này chưa từng có phiếu bảo hành.
    </div>
    @endif
</section>
@else
<section class="panel">
    <div class="p-4 text-center text-muted">
        Không tìm thấy IMEI:
        <strong>{{ request('imei') }}</strong>
    </div>
</section>
@endif
@endif
@endsection