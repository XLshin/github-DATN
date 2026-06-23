@extends('layouts.admin')

@section('title', 'Tạo phiếu bảo hành')
@section('page_icon', 'bi-shield-plus')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Tạo phiếu bảo hành')
@section('page_subtitle', 'Tạo phiếu bảo hành mới theo mã đơn hàng và IMEI sản phẩm.')

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

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin phiếu bảo hành</h5>
            <div class="text-muted small">
                Nhập mã đơn hàng, IMEI, thời hạn và trạng thái bảo hành.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form method="POST" action="{{ route('admin.warranties.store') }}" style="max-width: 800px;">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Mã đơn hàng <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="order_code"
                        value="{{ old('order_code') }}"
                        class="form-control @error('order_code') is-invalid @enderror"
                        placeholder="Nhập mã đơn hàng">

                    @error('order_code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        IMEI <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="imei"
                        value="{{ old('imei') }}"
                        class="form-control @error('imei') is-invalid @enderror"
                        placeholder="Nhập IMEI sản phẩm">

                    @error('imei')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Ngày bắt đầu <span class="text-danger">*</span>
                    </label>

                    <input
                        type="date"
                        name="warranty_start"
                        value="{{ old('warranty_start', now()->toDateString()) }}"
                        class="form-control @error('warranty_start') is-invalid @enderror">

                    @error('warranty_start')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Ngày kết thúc <span class="text-danger">*</span>
                    </label>

                    <input
                        type="date"
                        name="warranty_end"
                        value="{{ old('warranty_end', now()->addYear()->toDateString()) }}"
                        class="form-control @error('warranty_end') is-invalid @enderror">

                    @error('warranty_end')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Trạng thái <span class="text-danger">*</span>
                    </label>

                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" @selected(old('status', 'active' )==='active' )>
                            Còn bảo hành
                        </option>

                        <option value="claimed" @selected(old('status')==='claimed' )>
                            Đang bảo hành
                        </option>

                        <option value="expired" @selected(old('status')==='expired' )>
                            Hết hạn
                        </option>
                    </select>

                    @error('status')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Tạo phiếu
                </button>

                <a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>

@endsection