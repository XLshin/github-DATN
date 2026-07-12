@extends('layouts.admin')

@section('title', 'Tạo phiếu bảo hành')
@section('page_icon', 'bi-shield-plus')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Tạo phiếu bảo hành')
@section('page_subtitle', 'Chọn IMEI đã bán, hệ thống tự lấy thông tin sản phẩm và đơn hàng để tạo phiếu.')

@section('heading_actions')
<a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')

@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    Vui lòng kiểm tra lại thông tin tạo phiếu bảo hành.
</div>
@endif

<section class="panel mb-3">
    <div class="panel-header">
        <form method="GET" action="{{ route('admin.warranties.create') }}" class="row g-2 flex-grow-1">
            <div class="col-md-8">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm IMEI, sản phẩm, mã đơn, tên hoặc SĐT khách hàng">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Tìm kiếm
                </button>

                <a href="{{ route('admin.warranties.create') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>IMEI</th>
                    <th>Sản phẩm</th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Hạn bảo hành</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($soldImeis as $item)
                @php
                    $warrantyEnd = $item->order_created_at
                        ? \Carbon\Carbon::parse($item->order_created_at)->addYear()
                        : null;

                    $isWarrantyExpired = $warrantyEnd
                        ? now()->startOfDay()->gt($warrantyEnd->copy()->startOfDay())
                        : false;
                @endphp

                <tr>
                    <td class="fw-semibold">
                        {{ $item->imei }}
                    </td>

                    <td>
                        <div class="fw-semibold">
                            {{ $item->product_name }}
                        </div>
                        <div class="text-muted small">
                            {{ $item->color }} - {{ $item->storage }}
                        </div>
                    </td>

                    <td>
                        {{ $item->order_code ?? 'Chưa liên kết đơn' }}
                    </td>

                    <td>
                        <div>{{ $item->customer_name ?? 'Không có' }}</div>
                        <div class="text-muted small">{{ $item->customer_phone ?? '' }}</div>
                    </td>

                    <td>
                        @if($warrantyEnd)
                            <div>{{ $warrantyEnd->format('d/m/Y') }}</div>

                            @if($isWarrantyExpired)
                                <div class="text-danger small">Đã quá hạn</div>
                            @else
                                <div class="text-success small">Còn hạn</div>
                            @endif
                        @else
                            <span class="text-muted">Không xác định</span>
                        @endif
                    </td>

                    <td>
                        @if($item->open_warranty_id)
                            <span class="badge text-bg-warning">Đang có phiếu xử lý</span>
                        @elseif(!$item->order_id)
                            <span class="badge text-bg-danger">Thiếu đơn hàng</span>
                        @elseif($isWarrantyExpired)
                            <span class="badge text-bg-secondary">Quá hạn bảo hành</span>
                        @else
                            <span class="badge text-bg-primary">Có thể tạo phiếu</span>
                        @endif
                    </td>

                    <td class="text-end">
                        @if($item->open_warranty_id)
                            <a href="{{ route('admin.warranties.show', $item->open_warranty_id) }}" class="btn btn-light btn-sm">
                                Xem phiếu
                            </a>
                        @elseif(!$item->order_id)
                            <button type="button" class="btn btn-light btn-sm" disabled>
                                Chưa thể tạo
                            </button>
                        @elseif($isWarrantyExpired)
                            <button type="button" class="btn btn-light btn-sm" disabled>
                                Hết hạn BH
                            </button>
                        @else
                            <a href="{{ route('admin.warranties.create', ['imei_id' => $item->imei_id, 'keyword' => request('keyword')]) }}" class="btn btn-primary btn-sm">
                                Tạo phiếu BH
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa tìm thấy IMEI đã bán phù hợp.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($soldImeis->hasPages())
    <div class="p-3">
        {{ $soldImeis->links() }}
    </div>
    @endif
</section>

@if($selectedImei)
@php
    $defaultStart = $selectedImei->order_created_at
        ? \Carbon\Carbon::parse($selectedImei->order_created_at)->toDateString()
        : now()->toDateString();

    $defaultEnd = \Carbon\Carbon::parse($defaultStart)->addYear()->toDateString();
@endphp

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin tự lấy từ CSDL</h5>
                    <div class="text-muted small">
                        Dữ liệu sản phẩm, IMEI và đơn hàng đã bán.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">IMEI</div>
                    <div class="fw-semibold">
                        {{ $selectedImei->imei }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Sản phẩm</div>
                    <div class="fw-semibold">
                        {{ $selectedImei->product_name }}
                    </div>
                    <div class="text-muted small">
                        {{ $selectedImei->color }} - {{ $selectedImei->storage }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Mã đơn hàng</div>
                    <div class="fw-semibold">
                        {{ $selectedImei->order_code }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $selectedImei->customer_name }}
                    </div>
                    <div class="text-muted small">
                        {{ $selectedImei->customer_phone }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Địa chỉ giao hàng</div>
                    <div>
                        {{ $selectedImei->shipping_address ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Ngày mua / ngày tạo đơn</div>
                    <div class="fw-semibold">
                        {{ $selectedImei->order_created_at ? \Carbon\Carbon::parse($selectedImei->order_created_at)->format('d/m/Y') : 'Không có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Bảo hành đến</div>
                    <div class="fw-semibold">
                        {{ \Carbon\Carbon::parse($defaultEnd)->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Xác nhận tạo phiếu</h5>
                    <div class="text-muted small">
                        Khi tạo phiếu, hệ thống mặc định là đang xử lý bảo hành.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.store') }}" enctype="multipart/form-data" style="max-width: 760px;">
                    @csrf

                    <input type="hidden" name="imei_id" value="{{ $selectedImei->imei_id }}">
                    <input type="hidden" name="status" value="claimed">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Ngày bắt đầu bảo hành <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="warranty_start"
                                value="{{ old('warranty_start', $defaultStart) }}"
                                class="form-control @error('warranty_start') is-invalid @enderror"
                                readonly>

                            @error('warranty_start')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Ngày hết hạn bảo hành <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="warranty_end"
                                value="{{ old('warranty_end', $defaultEnd) }}"
                                class="form-control @error('warranty_end') is-invalid @enderror"
                                readonly>

                            @error('warranty_end')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Trạng thái xử lý
                            </label>

                            <div>
                                <span class="badge text-bg-warning px-3 py-2">
                                    Đang xử lý bảo hành
                                </span>
                            </div>

                            <div class="text-muted small mt-2">
                                Khi tạo phiếu, hệ thống mặc định xem đây là yêu cầu bảo hành đang được xử lý.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Lỗi khách báo / ghi chú tiếp nhận <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="customer_note"
                                rows="4"
                                class="form-control @error('customer_note') is-invalid @enderror"
                                placeholder="Ví dụ: Khách báo máy sạc không vào pin, thỉnh thoảng tự tắt nguồn.">{{ old('customer_note') }}</textarea>

                            @error('customer_note')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                            <div class="form-text">
                                Nhập tình trạng máy hoặc lỗi khách báo khi tiếp nhận bảo hành.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Ảnh/video tình trạng máy lúc tiếp nhận <span class="text-danger">*</span>
                            </label>

                            <input
                                type="file"
                                name="reception_media[]"
                                class="form-control @error('reception_media') is-invalid @enderror @error('reception_media.*') is-invalid @enderror"
                                accept="image/*,video/*"
                                multiple>

                            @error('reception_media')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror

                            @error('reception_media.*')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror

                            <div class="form-text">
                                Có thể chọn nhiều ảnh/video. Mỗi file tối đa 100MB.
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0">
                        Sau khi tạo phiếu, IMEI sẽ chuyển sang trạng thái <strong>warranty</strong>.
                        IMEI chỉ được tạo phiếu mới sau khi phiếu hiện tại được chuyển sang
                        <strong>Hoàn tất xử lý</strong> và IMEI vẫn còn thời hạn bảo hành thật sự.
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Tạo phiếu bảo hành
                        </button>

                        <a href="{{ route('admin.warranties.create') }}" class="btn btn-light btn-sm">
                            Hủy chọn IMEI
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@else
<section class="panel">
    <div class="p-4 text-center text-muted">
        Hãy chọn một IMEI đã bán trong danh sách phía trên để tạo phiếu bảo hành.
    </div>
</section>
@endif

@endsection