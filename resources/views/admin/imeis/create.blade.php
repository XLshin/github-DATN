@extends('layouts.admin')

@section('title', 'Thêm IMEI')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Thêm IMEI')
@section('page_subtitle', 'Thêm mã IMEI mới cho biến thể sản phẩm.')

@section('heading_actions')
<a href="{{ route('admin.imeis.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin IMEI</h5>
            <div class="text-muted small">
                Nhập Product Variant ID và mã IMEI của thiết bị.
            </div>
        </div>
    </div>

    <div class="p-3">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.imeis.store') }}" method="POST" style="max-width: 700px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Product Variant ID <span class="text-danger">*</span>
                </label>

                <input
                    type="number"
                    name="product_variant_id"
                    value="{{ old('product_variant_id') }}"
                    class="form-control @error('product_variant_id') is-invalid @enderror"
                    placeholder="Nhập ID biến thể sản phẩm">

                @error('product_variant_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    IMEI <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="imei"
                    value="{{ old('imei') }}"
                    class="form-control @error('imei') is-invalid @enderror"
                    placeholder="Nhập mã IMEI">

                @error('imei')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Lưu IMEI
                </button>

                <a href="{{ route('admin.imeis.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection