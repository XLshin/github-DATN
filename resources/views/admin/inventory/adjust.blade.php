@extends('layouts.admin')

@section('title', 'Điều chỉnh kho')
@section('page_icon', 'bi-sliders')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Điều chỉnh kho phụ kiện')
@section('page_subtitle', 'Dùng khi kiểm kê lệch, nhập nhầm thừa hoặc nhập thiếu số lượng phụ kiện.')

@section('heading_actions')
<a href="{{ route('admin.stocks.accessories') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i>
    Quay lại
</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Phiếu điều chỉnh kho</h5>
            <div class="text-muted small">
                Nhập số dương để cộng tồn, số âm để trừ tồn. Mỗi lần điều chỉnh sẽ được lưu vào lịch sử kho.
            </div>
        </div>
    </div>

    <div class="p-3">
        <div class="alert alert-info">
            <div class="fw-semibold">Lưu ý khi điều chỉnh</div>
            <div>Không sửa phiếu nhập cũ. Nếu nhập nhầm, tạo phiếu điều chỉnh mới kèm lý do để lịch sử kho không bị mất dấu.</div>
        </div>

        <form action="{{ route('admin.inventory.adjustments.store') }}" method="POST" style="max-width:760px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Biến thể phụ kiện <span class="text-danger">*</span>
                </label>

                <select
                    id="variantSelect"
                    name="product_variant_id"
                    class="form-select @error('product_variant_id') is-invalid @enderror">
                    <option value="">-- Chọn biến thể phụ kiện --</option>

                    @foreach($quantityVariants as $variant)
                        @php
                            $product = $variant->product;
                            $label = trim(($product?->name ?? 'Sản phẩm') . ' - ' . ($variant->color ?: 'Không màu'));
                            $selected = (string) old('product_variant_id', $selectedVariantId) === (string) $variant->id;
                        @endphp
                        <option
                            value="{{ $variant->id }}"
                            data-product="{{ $product?->name }}"
                            data-storage="{{ $product?->storage }}"
                            data-color="{{ $variant->color ?? '' }}"
                            data-stock="{{ $variant->stock_quantity }}"
                            {{ $selected ? 'selected' : '' }}>
                            {{ $label }} (Hiện còn: {{ $variant->stock_quantity }})
                        </option>
                    @endforeach
                </select>

                @error('product_variant_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Số lượng điều chỉnh <span class="text-danger">*</span>
                </label>

                <input
                    type="number"
                    name="quantity"
                    value="{{ old('quantity') }}"
                    class="form-control @error('quantity') is-invalid @enderror"
                    placeholder="Ví dụ: 5 nếu nhập thiếu, -5 nếu nhập thừa">

                @error('quantity')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Lý do điều chỉnh <span class="text-danger">*</span>
                </label>

                <textarea
                    name="note"
                    rows="4"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Ví dụ: Kiểm kê lệch ngày 11/07, nhập thừa 2 chiếc">{{ old('note') }}</textarea>

                @error('note')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i>
                    Lưu điều chỉnh
                </button>

                <a href="{{ route('admin.stocks.accessories') }}" class="btn btn-light btn-sm">
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

    const options = [];
    const selectedItems = [];

    Array.from(select.options).forEach(option => {
        if (!option.value) return;

        options.push({
            value: option.value,
            text: option.textContent.trim(),
            product: option.dataset.product || '',
            storage: option.dataset.storage || '',
            color: option.dataset.color || '',
            stock: option.dataset.stock || '0'
        });

        if (option.selected) selectedItems.push(option.value);
    });

    new TomSelect(select, {
        allowEmptyOption: true,
        plugins: ['clear_button'],
        placeholder: 'Tìm biến thể phụ kiện...',
        options: options,
        items: selectedItems,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text', 'product', 'storage', 'color'],
        maxOptions: null,
        openOnFocus: true,
        render: {
            option: function(data, escape) {
                if (!data.value) return '<div class="text-muted">' + escape(data.text) + '</div>';
                const meta = [data.storage, data.color, 'Tồn: ' + data.stock].filter(Boolean).join(' - ');
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
