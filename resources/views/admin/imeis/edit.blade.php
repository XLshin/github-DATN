@extends('layouts.admin')

@section('title', 'Sửa IMEI')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Sửa IMEI')
@section('page_subtitle', 'Cập nhật biến thể sản phẩm, mã IMEI và trạng thái thiết bị.')

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
                Chỉnh sửa Product Variant ID, mã IMEI và trạng thái của thiết bị.
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

        <form action="{{ route('admin.imeis.update', $imei->id) }}" method="POST" style="max-width: 700px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">
                    Product Variant ID <span class="text-danger">*</span>
                </label>

                <input
                    type="number"
                    name="product_variant_id"
                    value="{{ old('product_variant_id', $imei->product_variant_id) }}"
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
                    value="{{ old('imei', $imei->imei) }}"
                    class="form-control @error('imei') is-invalid @enderror"
                    placeholder="Nhập mã IMEI">

                @error('imei')
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
                    <option value="available" @selected(old('status', $imei->status) === 'available')>
                        Còn hàng
                    </option>

                    <option value="sold" @selected(old('status', $imei->status) === 'sold')>
                        Đã bán
                    </option>

                    <option value="warranty" @selected(old('status', $imei->status) === 'warranty')>
                        Bảo hành
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
                    <i class="bi bi-check-lg"></i> Cập nhật
                </button>

                <a href="{{ route('admin.imeis.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection