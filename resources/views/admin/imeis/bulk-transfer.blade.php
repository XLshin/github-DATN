@extends('layouts.admin')

@section('title', 'Chuyển IMEI hàng loạt')
@section('page_icon', 'bi-arrow-left-right')
@section('page_eyebrow', 'Kho IMEI/Serial')
@section('page_title', 'Chuyển IMEI hàng loạt')
@section('page_subtitle', 'Dùng khi nhập nhầm IMEI vào sai biến thể. Chỉ admin được thực hiện thao tác này.')

@section('heading_actions')
<a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i>
    Quay lại kho
</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin chuyển kho</h5>
            <div class="text-muted small">
                Dán danh sách IMEI đang nhập sai, sau đó chọn biến thể đúng để chuyển sang.
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

        <div class="alert alert-warning d-flex gap-2 align-items-start">
            <i class="bi bi-exclamation-triangle mt-1"></i>
            <div>
                <div class="fw-semibold">Chỉ chuyển được IMEI còn hàng</div>
                <div class="small">
                    IMEI đã giữ chỗ, đã bán, đang bảo hành hoặc đã loại khỏi kho sẽ bị chặn để tránh lệch nghiệp vụ.
                    Hệ thống sẽ ghi lịch sử kho cho cả biến thể cũ và biến thể mới.
                </div>
            </div>
        </div>

        <form action="{{ route('admin.imeis.bulk-transfer.store') }}" method="POST" enctype="multipart/form-data" style="max-width:760px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Biến thể đích
                    <span class="text-danger">*</span>
                </label>

                <select
                    id="targetVariantSelect"
                    name="target_product_variant_id"
                    class="form-select @error('target_product_variant_id') is-invalid @enderror">
                    <option value="">-- Chọn biến thể đúng --</option>

                    @foreach($imeiVariants as $variant)
                        <option
                            value="{{ $variant->id }}"
                            data-product="{{ $variant->product->name }}"
                            data-color="{{ $variant->color ?? '' }}"
                            data-storage="{{ $variant->product->storage ?? '' }}"
                            @selected(old('target_product_variant_id') == $variant->id)>
                            {{ trim($variant->product->name . ' - ' . ($variant->color ?? '---') . ($variant->product->storage ? ' - ' . $variant->product->storage : '')) }}
                        </option>
                    @endforeach
                </select>

                @error('target_product_variant_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Danh sách IMEI cần chuyển
                </label>

                <textarea
                    name="imeis"
                    rows="7"
                    class="form-control @error('imeis') is-invalid @enderror"
                    placeholder="Mỗi dòng một IMEI&#10;123456789012345&#10;123456789012346">{{ old('imeis') }}</textarea>
                <div class="form-text">
                    Dán các IMEI đã nhập nhầm, mỗi dòng một mã.
                </div>

                @error('imeis')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    File IMEI cần chuyển
                </label>
                <input
                    type="file"
                    name="imei_file"
                    class="form-control @error('imei_file') is-invalid @enderror"
                    accept=".xlsx,.csv,.txt">
                <div class="form-text">
                    Có thể upload lại file Excel/csv/txt đã nhập nhầm. Nếu dùng Excel, hệ thống đọc IMEI ở cột A.
                </div>

                @error('imei_file')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Lý do chuyển
                    <span class="text-danger">*</span>
                </label>

                <textarea
                    name="note"
                    rows="3"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Ví dụ: Nhập nhầm file Excel của biến thể khác">{{ old('note') }}</textarea>

                @error('note')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button
                    type="submit"
                    class="btn btn-primary btn-sm"
                    onclick="return confirm('Xác nhận chuyển các IMEI này sang biến thể đã chọn?')">
                    <i class="bi bi-arrow-left-right"></i>
                    Xác nhận chuyển
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
    const select = document.getElementById('targetVariantSelect');
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
        placeholder: 'Tìm biến thể đúng...',
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
                return '<div><div class="fw-semibold">' + escape(data.product || data.text) + '</div><div class="small text-muted">' + escape(meta || 'Chưa có màu/dung lượng') + '</div></div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        }
    });
});
</script>
@endpush
