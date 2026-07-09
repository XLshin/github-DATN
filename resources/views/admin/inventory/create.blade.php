@extends('layouts.admin')

@section('title', 'Nhập kho')
@section('page_icon', 'bi-box-arrow-in-down')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Nhập kho')
@section('page_subtitle', 'Nhập kho cho các sản phẩm quản lý bằng số lượng.')

@section('heading_actions')
<a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
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
                Chọn biến thể phụ kiện, nhập số lượng và ghi chú.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form action="{{ route('admin.inventory.store') }}" method="POST" style="max-width:700px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Chọn biến thể phụ kiện
                    <span class="text-danger">*</span>
                </label>

                <select
                    id="variantSelect"
                    name="product_variant_id"
                    class="form-select @error('product_variant_id') is-invalid @enderror">
                    <option value="">-- Chọn biến thể phụ kiện --</option>

                    @foreach($quantityVariants as $variant)
                        <option
                            value="{{ $variant->id }}"
                            data-product="{{ $variant->product->name }}"
                            data-color="{{ $variant->color ?? '' }}"
                            data-stock="{{ $variant->stock_quantity }}"
                            {{ old('product_variant_id') == $variant->id ? 'selected' : '' }}>
                            {{ trim(
                                $variant->product->name .
                                ' - ' .
                                ($variant->color ?? '---') .
                                ' (Hiện còn: ' . $variant->stock_quantity . ')'
                            ) }}
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
                    Số lượng <span class="text-danger">*</span>
                </label>

                <input
                    type="number"
                    name="quantity"
                    value="{{ old('quantity') }}"
                    class="form-control @error('quantity') is-invalid @enderror"
                    placeholder="Nhập số lượng">

                @error('quantity')
                    <div class="invalid-feedback d-block">
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
                    placeholder="Nhập ghi chú">{{ old('note') }}</textarea>

                @error('note')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i>
                    Luu nhap kho
                </button>

                <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
                    Huy
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
            stock: option.dataset.stock || '0'
        });

        if (option.selected) {
            selectedItems.push(option.value);
        }
    });

    new TomSelect(select, {
        allowEmptyOption: true,
        plugins: ['clear_button'],
        placeholder: 'Tìm biến thể phụ kiện...',
        options: variantOptions,
        items: selectedItems,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text', 'product', 'color'],
        maxOptions: null,
        openOnFocus: true,
        render: {
            option: function(data, escape) {
                if (!data.value) return '<div class="text-muted">' + escape(data.text) + '</div>';
                const meta = [data.color].filter(Boolean).join(' - ');
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
