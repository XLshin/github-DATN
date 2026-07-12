@extends('layouts.admin')

@section('title', 'Nhập kho IMEI / Serial')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Nhập kho IMEI / Serial')
@section('page_subtitle', 'Nhập kho cho các sản phẩm quản lý bằng IMEI hoặc Serial.')

@section('heading_actions')
<a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i>
    Quay lai
</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin nhập kho</h5>
            <div class="text-muted small">
                Chọn biến thể sản phẩm và nhập danh sách IMEI / Serial.
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

        <form action="{{ route('admin.imeis.store') }}" method="POST" style="max-width:700px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Chọn biến thể sản phẩm
                    <span class="text-danger">*</span>
                </label>

                <select
                    id="variantSelect"
                    name="product_variant_id"
                    class="form-select @error('product_variant_id') is-invalid @enderror">
                    <option value="">-- Chọn biến thể sản phẩm --</option>

                    @foreach($imeiVariants as $variant)
                        <option
                            value="{{ $variant->id }}"
                            data-product="{{ $variant->product->name }}"
                            data-color="{{ $variant->color ?? '' }}"
                            data-storage="{{ $variant->product->storage ?? '' }}"
                            {{ old('product_variant_id') == $variant->id ? 'selected' : '' }}>
                            {{ trim($variant->product->name . ' - ' . ($variant->color ?? '---') . ($variant->product->storage ? ' - ' . $variant->product->storage : '')) }}
                        </option>
                    @endforeach
                </select>

                @error('product_variant_id')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Danh sách IMEI / Serial
                    <span class="text-danger">*</span>
                </label>

                <textarea
                    name="imeis"
                    rows="5"
                    class="form-control @error('imeis') is-invalid @enderror"
                    placeholder="Mỗi dòng một IMEI hoặc Serial&#10;123456789012345&#10;123456789012346&#10;123456789012347">{{ old('imeis') }}</textarea>
                <div class="form-text">
                    Mỗi dòng nhập một IMEI hoặc Serial.
                </div>

                @error('imeis')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i>
                    Nhập kho
                </button>

                <a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('variantSelect');
    if (!select || !window.TomSelect) return;

    const variantOptions = [];
    const selectedItems = [];

    Array.from(select.options).forEach(option => {
        if (!option.value) return;

        variantOptions.push({
            value: option.value,
            text: option.textContent.trim(),
            product: option.dataset.product || '',
            color: option.dataset.color || '',
            storage: option.dataset.storage || ''
        });

        if (option.selected) {
            selectedItems.push(option.value);
        }
    });

    new TomSelect(select, {
        allowEmptyOption: true,
        plugins: ['clear_button'],
        placeholder: 'Tìm biến thể sản phẩm...',
        options: variantOptions,
        items: selectedItems,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text', 'product', 'color', 'storage'],
        maxOptions: null,
        openOnFocus: true,
        render: {
            option: function(data, escape) {
                if (!data.value) return '<div class="text-muted">' + escape(data.text) + '</div>';
                const meta = [data.color, data.storage].filter(Boolean).join(' - ');
                return '<div><div class="fw-semibold">' + escape(data.product || data.text) + '</div><div class="small text-muted">' + escape(meta) + '</div></div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        }
    });
});
</script>
@endpush
