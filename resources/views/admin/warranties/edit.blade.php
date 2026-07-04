@extends('layouts.admin')

@section('title', 'Sửa phiếu bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Sửa phiếu bảo hành')
@section('page_subtitle', 'Cập nhật thời hạn, lỗi khách báo và trạng thái xử lý của phiếu bảo hành.')

@section('heading_actions')
<a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại chi tiết
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
    Vui lòng kiểm tra lại thông tin cập nhật phiếu bảo hành.
</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin phiếu</h5>
                    <div class="text-muted small">
                        Thông tin đơn hàng, sản phẩm và IMEI liên quan.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã phiếu</div>
                    <div class="fw-semibold">
                        {{ $warranty->warranty_code }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">IMEI</div>
                    <div class="fw-semibold">
                        {{ $warranty->imei->imei ?? $warrantyDetail->imei ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Sản phẩm</div>

                    @if($warrantyDetail)
                        <div class="fw-semibold">
                            {{ $warrantyDetail->product_name }}
                        </div>
                        <div class="text-muted small">
                            {{ $warrantyDetail->color }} - {{ $warrantyDetail->storage }}
                        </div>
                    @else
                        <div class="fw-semibold">Không có</div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Mã đơn</div>
                    <div class="fw-semibold">
                        {{ $warranty->order->order_code ?? $warrantyDetail->order_code ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $warranty->order->customer_name ?? $warrantyDetail->customer_name ?? 'Không có' }}
                    </div>
                    <div class="text-muted small">
                        {{ $warranty->order->customer_phone ?? $warrantyDetail->customer_phone ?? '' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Trạng thái hiện tại</div>
                    <span class="badge text-bg-{{ $warranty->status_badge }}">
                        {{ $warranty->status_label }}
                    </span>
                </div>

                <div>
                    <div class="text-muted small">Trạng thái IMEI</div>
                    <div class="fw-semibold">
                        {{ $warrantyDetail->imei_status ?? $warranty->imei->status ?? 'Không có' }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Cập nhật bảo hành</h5>
                    <div class="text-muted small">
                        Chỉnh sửa ngày bắt đầu, ngày hết hạn, lỗi khách báo và trạng thái xử lý.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.update', $warranty) }}" style="max-width: 700px;">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">
                            Ngày bắt đầu bảo hành <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="warranty_start"
                            value="{{ old('warranty_start', $warranty->warranty_start ? $warranty->warranty_start->format('Y-m-d') : '') }}"
                            class="form-control @error('warranty_start') is-invalid  @enderror" readonly >

                        @error('warranty_start')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ngày hết hạn bảo hành <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="warranty_end"
                            value="{{ old('warranty_end', $warranty->warranty_end ? $warranty->warranty_end->format('Y-m-d') : '') }}"
                            class="form-control @error('warranty_end') is-invalid @enderror" readonly >

                        @error('warranty_end')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Đây là hạn bảo hành thật sự của IMEI. Hết hạn này thì không tạo phiếu mới được.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Trạng thái xử lý <span class="text-danger">*</span>
                        </label>

                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="claimed" @selected(old('status', $warranty->status) === 'claimed')>
                                Đang xử lý bảo hành
                            </option>

                            <option value="active" @selected(in_array(old('status', $warranty->status), ['active', 'expired'], true))>
                                Hoàn tất xử lý
                            </option>
                        </select>

                        @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Lỗi khách báo / ghi chú tiếp nhận
                        </label>

                        <textarea
                            name="customer_note"
                            rows="4"
                            class="form-control @error('customer_note') is-invalid @enderror"
                            placeholder="Ví dụ: Khách báo máy sạc không vào pin, thỉnh thoảng tự tắt nguồn.">{{ old('customer_note', $warranty->customer_note) }}</textarea>

                        @error('customer_note')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Có thể chỉnh sửa ghi chú nếu lúc tiếp nhận nhập thiếu hoặc nhập sai.
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Đang xử lý bảo hành</strong>: IMEI sẽ ở trạng thái <strong>warranty</strong> và chưa được tạo phiếu mới.<br>
                        <strong>Hoàn tất xử lý</strong>: IMEI chuyển về <strong>sold</strong>, sau này vẫn có thể tạo phiếu mới nếu còn thời hạn bảo hành thật sự.<br>
                        <strong>Hết hạn bảo hành</strong>: hệ thống tự kiểm tra theo ngày mua + 12 tháng, admin không cần chọn thủ công.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Lưu thay đổi
                        </button>

                        <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

@endsection