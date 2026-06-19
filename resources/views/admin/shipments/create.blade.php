@extends('layouts.admin')

@section('title', 'Tạo vận đơn')
@section('page_icon', 'bi-truck')
@section('page_eyebrow', 'Quản lý vận chuyển')
@section('page_title', 'Tạo vận đơn')
@section('page_subtitle', 'Tạo mã vận đơn cho đơn hàng đã sẵn sàng giao.')

@section('heading_actions')
<a href="{{ route('admin.shipments.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin đơn hàng</h5>
                    <div class="text-muted small">
                        Kiểm tra thông tin khách hàng trước khi tạo vận đơn.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã đơn</div>
                    <div class="fw-semibold">
                        {{ $order->order_code }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $order->customer_name }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">SĐT</div>
                    <div class="fw-semibold">
                        {{ $order->customer_phone }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Địa chỉ</div>
                    <div class="fw-semibold">
                        {{ $order->shipping_address }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Trạng thái đơn</div>
                    <span class="badge text-bg-secondary">
                        {{ $order->status }}
                    </span>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin vận đơn</h5>
                    <div class="text-muted small">
                        Nhập đơn vị vận chuyển và mã vận đơn.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.shipments.storeFromOrder', $order) }}" style="max-width: 700px;">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Đơn vị vận chuyển <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="shipping_unit"
                            value="{{ old('shipping_unit') }}"
                            class="form-control @error('shipping_unit') is-invalid @enderror"
                            placeholder="Ví dụ: GHN, GHTK, Viettel Post">

                        @error('shipping_unit')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Mã vận đơn
                        </label>

                        <input
                            type="text"
                            name="tracking_code"
                            value="{{ old('tracking_code') }}"
                            class="form-control @error('tracking_code') is-invalid @enderror"
                            placeholder="Nhập mã vận đơn nếu có">

                        @error('tracking_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Tạo vận đơn
                        </button>

                        <a href="{{ route('admin.shipments.index') }}" class="btn btn-light btn-sm">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

@endsection