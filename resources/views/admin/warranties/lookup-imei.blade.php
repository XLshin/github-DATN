@extends('layouts.admin')

@section('title', 'Tra cứu bảo hành')
@section('page_icon', 'bi-search')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Tra cứu bảo hành theo IMEI')
@section('page_subtitle', 'Kiểm tra thông tin IMEI, đơn hàng đã bán, lỗi khách báo, phiếu bảo hành hiện tại và lịch sử bảo hành.')

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

                        @if($imeiDetail)
                            <div class="mb-3">
                                <div class="text-muted small">Sản phẩm</div>
                                <div class="fw-semibold">
                                    {{ $imeiDetail->product_name }}
                                </div>
                                <div class="text-muted small">
                                    {{ $imeiDetail->color }} - {{ $imeiDetail->storage }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Mã đơn hàng</div>
                                <div class="fw-semibold">
                                    {{ $imeiDetail->order_code ?? 'Chưa liên kết đơn' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Khách hàng</div>
                                <div class="fw-semibold">
                                    {{ $imeiDetail->customer_name ?? 'Không có' }}
                                </div>
                                <div class="text-muted small">
                                    {{ $imeiDetail->customer_phone ?? '' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Ngày mua / ngày tạo đơn</div>
                                <div class="fw-semibold">
                                    {{ $imeiDetail->order_created_at ? \Carbon\Carbon::parse($imeiDetail->order_created_at)->format('d/m/Y') : 'Không có' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small">Hạn bảo hành thật sự</div>

                                @if($imeiDetail->order_created_at)
                                    @php
                                        $warrantyEnd = \Carbon\Carbon::parse($imeiDetail->order_created_at)->addYear();
                                    @endphp

                                    <div class="fw-semibold">
                                        {{ $warrantyEnd->format('d/m/Y') }}
                                    </div>

                                    @if(now()->startOfDay()->gt($warrantyEnd->copy()->startOfDay()))
                                        <div class="text-danger small">
                                            IMEI đã quá hạn bảo hành
                                        </div>
                                    @else
                                        <div class="text-success small">
                                            IMEI còn trong thời hạn bảo hành
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted">
                                        Không xác định
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div>
                            <div class="text-muted small">Trạng thái IMEI</div>

                            @if($imei->status === 'available')
                                <span class="badge text-bg-success">Còn hàng</span>
                            @elseif($imei->status === 'reserved')
                                <span class="badge text-bg-info">Đang giữ hàng</span>
                            @elseif($imei->status === 'sold')
                                <span class="badge text-bg-primary">Đã bán</span>
                            @elseif($imei->status === 'warranty')
                                <span class="badge text-bg-warning">Đang bảo hành</span>
                            @elseif($imei->status === 'returned')
                                <span class="badge text-bg-secondary">Đã trả hàng</span>
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
                            <h5 class="mb-1">Phiếu bảo hành đang xử lý</h5>
                            <div class="text-muted small">
                                Chỉ hiển thị phiếu đang ở trạng thái Đang xử lý bảo hành.
                            </div>
                        </div>
                    </div>

                    <div class="p-3">
                        @if ($currentWarranty)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted small">Mã phiếu</div>
                                    <div class="fw-semibold">
                                        {{ $currentWarranty->warranty_code }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Mã đơn</div>
                                    <div class="fw-semibold">
                                        {{ $currentWarranty->order->order_code ?? $currentWarrantyDetail->order_code ?? 'Không có' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Ngày bắt đầu bảo hành</div>
                                    <div class="fw-semibold">
                                        {{ $currentWarranty->warranty_start ? $currentWarranty->warranty_start->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Ngày hết hạn bảo hành</div>
                                    <div class="fw-semibold">
                                        {{ $currentWarranty->warranty_end ? $currentWarranty->warranty_end->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="text-muted small">Trạng thái xử lý</div>
                                    <span class="badge text-bg-{{ $currentWarranty->status_badge }}">
                                        {{ $currentWarranty->status_label }}
                                    </span>
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <a href="{{ route('admin.warranties.show', $currentWarranty) }}" class="btn btn-primary btn-sm">
                                        Xem chi tiết
                                    </a>
                                </div>

                                <div class="col-12">
                                    <div class="text-muted small mb-1">Lỗi khách báo / ghi chú tiếp nhận</div>

                                    @if($currentWarranty->customer_note)
                                        <div class="border rounded p-3 bg-light">
                                            {!! nl2br(e($currentWarranty->customer_note)) !!}
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            Chưa có ghi chú.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-muted mb-3">
                                IMEI này hiện không có phiếu bảo hành đang xử lý.
                            </div>

                            @if($imei->status === 'sold' && $imeiDetail && $imeiDetail->order_id)
                                @php
                                    $canCreateWarranty = true;

                                    if ($imeiDetail->order_created_at) {
                                        $warrantyEnd = \Carbon\Carbon::parse($imeiDetail->order_created_at)->addYear();
                                        $canCreateWarranty = now()->startOfDay()->lte($warrantyEnd->copy()->startOfDay());
                                    }
                                @endphp

                                @if($canCreateWarranty)
                                    <a href="{{ route('admin.warranties.create', ['imei_id' => $imei->id]) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg"></i> Tạo phiếu bảo hành cho IMEI này
                                    </a>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        IMEI này đã quá thời hạn bảo hành, không thể tạo phiếu bảo hành mới.
                                    </div>
                                @endif
                            @elseif($imei->status !== 'sold')
                                <div class="alert alert-warning mb-0">
                                    Chỉ tạo phiếu bảo hành trực tiếp cho IMEI có trạng thái <strong>Đã bán</strong>.
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    IMEI này chưa liên kết được với đơn hàng, nên chưa thể tạo phiếu bảo hành.
                                </div>
                            @endif
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
                                <th>Mã phiếu</th>
                                <th>Mã đơn</th>
                                <th>Lỗi khách báo</th>
                                <th>Bắt đầu</th>
                                <th>Hết hạn</th>
                                <th>Trạng thái xử lý</th>
                                <th>Ngày tạo phiếu</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($warranties as $warranty)
                                @php
                                    $detail = $warrantyDetails[$warranty->id] ?? null;
                                    $isWarrantyExpired = $warranty->warranty_end
                                        ? now()->startOfDay()->gt($warranty->warranty_end->copy()->startOfDay())
                                        : false;
                                @endphp

                                <tr>
                                    <td class="fw-semibold">
                                        {{ $warranty->warranty_code }}
                                    </td>

                                    <td>
                                        {{ $warranty->order->order_code ?? $detail->order_code ?? 'Không có' }}
                                    </td>

                                    <td style="max-width: 240px;">
                                        @if($warranty->customer_note)
                                            <div class="small">
                                                {{ \Illuminate\Support\Str::limit($warranty->customer_note, 90) }}
                                            </div>
                                        @else
                                            <span class="text-muted small">
                                                Chưa có ghi chú
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $warranty->warranty_start ? $warranty->warranty_start->format('d/m/Y') : 'N/A' }}
                                    </td>

                                    <td>
                                        <div>
                                            {{ $warranty->warranty_end ? $warranty->warranty_end->format('d/m/Y') : 'N/A' }}
                                        </div>

                                        @if($isWarrantyExpired)
                                            <div class="text-danger small">
                                                IMEI đã quá hạn
                                            </div>
                                        @elseif($warranty->warranty_end)
                                            <div class="text-success small">
                                                IMEI còn hạn
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge text-bg-{{ $warranty->status_badge }}">
                                            {{ $warranty->status_label }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $warranty->created_at ? $warranty->created_at->format('d/m/Y H:i') : 'N/A' }}
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