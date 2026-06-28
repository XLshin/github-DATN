@extends('layouts.admin')

@section('title', 'Nhập kho')
@section('page_icon', 'bi-box-arrow-in-down')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Nhập kho')
@section('page_subtitle', 'Tạo giao dịch nhập kho cho biến thể sản phẩm.')

@section('heading_actions')
<a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin nhập kho</h5>
            <div class="text-muted small">
                Nhập Product Variant ID, số lượng và ghi chú cho giao dịch nhập kho.
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

        <form action="{{ route('admin.inventory.store') }}" method="POST" style="max-width: 700px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Biến thể sản phẩm <span class="text-danger">*</span>
                </label>

                <select
                    name="product_variant_id"
                    class="form-select @error('product_variant_id') is-invalid @enderror">
                    <option value="">-- Chọn biến thể --</option>
                    @foreach($variants as $variant)
                    <option value="{{ $variant->id }}" {{ old('product_variant_id') == $variant->id ? 'selected' : '' }}>
                        [ID: {{ $variant->id }}] {{ $variant->product->name ?? '?' }} — {{ $variant->color }} / {{ $variant->storage }}
                    </option>
                    @endforeach
                </select>

                @error('product_variant_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Loại giao dịch
                </label>

                <input
                    type="text"
                    value="Nhập kho"
                    class="form-control"
                    readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Số lượng <span class="text-danger">*</span>
                </label>

                <input
                    type="number"
                    name="quantity"
                    value="{{ old('quantity') }}"
                    class="form-control @error('quantity') is-invalid @enderror"
                    placeholder="Nhập số lượng">

                @error('quantity')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Ghi chú
                </label>

                <textarea
                    name="note"
                    rows="4"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Nhập ghi chú nếu có">{{ old('note') }}</textarea>

                @error('note')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Lưu nhập kho
                </button>

                <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection