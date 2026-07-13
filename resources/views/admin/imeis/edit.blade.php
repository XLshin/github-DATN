@extends('layouts.admin')

@section('title', 'Điều chỉnh IMEI')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho IMEI/Serial')
@section('page_title', 'Điều chỉnh IMEI')
@section('page_subtitle', 'Dùng khi nhập nhầm IMEI, chọn sai biến thể hoặc cần loại IMEI nhập nhầm khỏi kho.')

@section('heading_actions')
<a href="{{ route('admin.imeis.show', $imei->id) }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin điều chỉnh</h5>
            <div class="text-muted small">
                Chỉ IMEI còn hàng, chưa giữ chỗ, chưa bán và chưa bảo hành mới được điều chỉnh.
            </div>
        </div>
    </div>

    <div class="p-3">
        @if (!$canAdjust)
            <div class="alert alert-warning">
                <div class="fw-semibold">IMEI này đang bị khóa chỉnh sửa</div>
                <div>IMEI đã giữ chỗ, đã bán hoặc đã có bảo hành không được sửa để tránh sai lịch sử đơn hàng/bảo hành.</div>
            </div>
        @else
            <div class="alert alert-info">
                <div class="fw-semibold">Lưu ý khi điều chỉnh IMEI</div>
                <div>Nếu nhập nhầm IMEI rác, hãy đổi trạng thái thành <strong>Nhập nhầm / loại khỏi kho</strong> thay vì xóa cứng.</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.imeis.update', $imei->id) }}" method="POST" style="max-width: 780px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Biến thể sản phẩm <span class="text-danger">*</span></label>
                <select
                    id="variantSelect"
                    name="product_variant_id"
                    class="form-select @error('product_variant_id') is-invalid @enderror"
                    {{ $canAdjust ? '' : 'disabled' }}>
                    @foreach($imeiVariants as $variant)
                        @php
                            $product = $variant->product;
                            $selected = (int) old('product_variant_id', $imei->product_variant_id) === (int) $variant->id;
                        @endphp
                        <option
                            value="{{ $variant->id }}"
                            data-product="{{ $product?->name }}"
                            data-storage="{{ $product?->storage }}"
                            data-color="{{ $variant->color ?? '' }}"
                            {{ $selected ? 'selected' : '' }}>
                            {{ $product?->name }} - {{ $product?->storage ?? '-' }} - {{ $variant->color ?: 'Không màu' }}
                        </option>
                    @endforeach
                </select>

                @if(!$canAdjust)
                    <input type="hidden" name="product_variant_id" value="{{ $imei->product_variant_id }}">
                @endif

                @error('product_variant_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">IMEI/Serial <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="imei"
                    value="{{ old('imei', $imei->imei) }}"
                    class="form-control @error('imei') is-invalid @enderror"
                    placeholder="Nhập mã IMEI gồm 15 số"
                    {{ $canAdjust ? '' : 'readonly' }}>

                @error('imei')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái điều chỉnh <span class="text-danger">*</span></label>
                <select
                    name="status"
                    class="form-select @error('status') is-invalid @enderror"
                    {{ $canAdjust ? '' : 'disabled' }}>
                    <option value="available" @selected(old('status', $imei->status) === 'available')>
                        Còn hàng
                    </option>
                    <option value="returned" @selected(old('status', $imei->status) === 'returned')>
                        Nhập nhầm / loại khỏi kho
                    </option>
                </select>

                @if(!$canAdjust)
                    <input type="hidden" name="status" value="{{ $imei->status }}">
                @endif

                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lý do điều chỉnh <span class="text-danger">*</span></label>
                <textarea
                    name="note"
                    rows="4"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Ví dụ: Nhập nhầm 1 số cuối IMEI, chuyển sai màu, hoặc IMEI nhập nhầm cần loại khỏi kho"
                    {{ $canAdjust ? '' : 'readonly' }}>{{ old('note') }}</textarea>

                @error('note')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm" {{ $canAdjust ? '' : 'disabled' }}>
                    <i class="bi bi-check-lg"></i> Lưu điều chỉnh
                </button>

                <a href="{{ route('admin.imeis.show', $imei->id) }}" class="btn btn-light btn-sm">
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
    if (!select || !window.TomSelect || select.disabled) return;

    const options = [];
    const selectedItems = [];

    Array.from(select.options).forEach(option => {
        options.push({
            value: option.value,
            text: option.textContent.trim(),
            product: option.dataset.product || '',
            storage: option.dataset.storage || '',
            color: option.dataset.color || ''
        });

        if (option.selected) selectedItems.push(option.value);
    });

    new TomSelect(select, {
        plugins: ['clear_button'],
        placeholder: 'Tìm biến thể sản phẩm...',
        options: options,
        items: selectedItems,
        valueField: 'value',
        labelField: 'text',
        searchField: ['text', 'product', 'storage', 'color'],
        maxOptions: null,
        openOnFocus: true,
        render: {
            option: function(data, escape) {
                const meta = [data.storage, data.color].filter(Boolean).join(' - ');
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
