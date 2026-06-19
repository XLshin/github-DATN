@extends('layouts.admin')

@section('title', 'Sửa phiếu bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Sửa phiếu bảo hành')
@section('page_subtitle', 'Cập nhật thời hạn và trạng thái của phiếu bảo hành.')

@section('heading_actions')
<a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin phiếu</h5>
                    <div class="text-muted small">
                        Thông tin đơn hàng và IMEI liên quan.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">IMEI</div>
                    <div class="fw-semibold">
                        {{ $warranty->imei->imei ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Mã đơn</div>
                    <div class="fw-semibold">
                        {{ $warranty->order->order_code ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $warranty->order->customer_name ?? 'Không có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Trạng thái hiện tại</div>

                    @if($warranty->status === 'active')
                    <span class="badge text-bg-success">Còn bảo hành</span>
                    @elseif($warranty->status === 'expired')
                    <span class="badge text-bg-secondary">Hết hạn</span>
                    @elseif($warranty->status === 'claimed')
                    <span class="badge text-bg-warning">Đang bảo hành</span>
                    @else
                    <span class="badge text-bg-light">
                        {{ $warranty->status }}
                    </span>
                    @endif
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
                        Chỉnh sửa ngày bắt đầu, ngày kết thúc và trạng thái bảo hành.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.update', $warranty) }}" style="max-width: 700px;">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">
                            Ngày bắt đầu <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="warranty_start"
                            value="{{ old('warranty_start', $warranty->warranty_start ? \Carbon\Carbon::parse($warranty->warranty_start)->format('Y-m-d') : '') }}"
                            class="form-control @error('warranty_start') is-invalid @enderror">

                        @error('warranty_start')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ngày kết thúc <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="warranty_end"
                            value="{{ old('warranty_end', $warranty->warranty_end ? \Carbon\Carbon::parse($warranty->warranty_end)->format('Y-m-d') : '') }}"
                            class="form-control @error('warranty_end') is-invalid @enderror">

                        @error('warranty_end')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Trạng thái <span class="text-danger">*</span>
                        </label>

                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', $warranty->status) === 'active')>
                                Còn bảo hành
                            </option>

                            <option value="expired" @selected(old('status', $warranty->status) === 'expired')>
                                Hết hạn
                            </option>

                            <option value="claimed" @selected(old('status', $warranty->status) === 'claimed')>
                                Đang bảo hành
                            </option>
                        </select>

                        @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Lưu thay đổi
                        </button>

                        <a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection