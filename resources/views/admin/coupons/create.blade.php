@extends('layouts.admin')

@section('title', 'Tạo voucher')
@section('page_icon', 'bi-ticket-perforated')
@section('page_eyebrow', 'Khuyến mại')
@section('page_title', 'Tạo voucher mới')
@section('page_subtitle', 'Tạo mã voucher để khách hàng sử dụng khi thanh toán.')

@section('heading_actions')
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h5 class="mb-1">Thông tin voucher</h5>
                <div class="text-muted small">Nhập mã, loại giảm giá, điều kiện và thời hạn.</div>
            </div>
        </div>

        <div class="p-3">
            <form action="{{ route('admin.coupons.store') }}" method="POST" style="max-width: 700px;">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Mã voucher <span class="text-danger">*</span></label>
                    <input name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="VD: BYTE10">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                            <option value="percent" @selected(old('discount_type')==='percent')>Phần trăm</option>
                            <option value="fixed" @selected(old('discount_type')==='fixed')>Cố định</option>
                        </select>
                        @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                        <input name="discount_value" value="{{ old('discount_value') }}" class="form-control @error('discount_value') is-invalid @enderror" placeholder="VD: 10 hoặc 100000">
                        @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Đơn tối thiểu</label>
                        <input name="min_order_amount" value="{{ old('min_order_amount', 0) }}" class="form-control @error('min_order_amount') is-invalid @enderror" placeholder="Số tiền tối thiểu">
                        @error('min_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hạn mức sử dụng</label>
                        <input name="usage_limit" value="{{ old('usage_limit', 0) }}" class="form-control @error('usage_limit') is-invalid @enderror" placeholder="0 = không giới hạn">
                        @error('usage_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control @error('start_date') is-invalid @enderror">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', '1')==='1')>Kích hoạt</option>
                        <option value="0" @selected(old('status')==='0')>Tắt</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Lưu voucher</button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-light btn-sm">Hủy</a>
                </div>
            </form>
        </div>
    </section>
@endsection
