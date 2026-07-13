@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')
@section('page_icon', 'bi-plus-circle')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm mới. Mỗi sản phẩm phải có ít nhất một biến thể.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm" data-turbo="false">
    @csrf

    <input type="hidden" name="stock_quantity" value="0">

    <div class="row g-3">
        <div class="col-lg-8">
            {{-- Thông tin cơ bản --}}
            <section class="panel mb-3">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Thông tin sản phẩm</h5>
                        <div class="text-muted small">Nhập tên, danh mục, thương hiệu và mô tả.</div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="mb-3">
                        <label class="form-label">Dòng sản phẩm <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="product_group_id" id="productGroupSelect" class="form-select @error('product_group_id') is-invalid @enderror">
                                <option value="">-- Chọn dòng sản phẩm --</option>
                                @foreach($productGroups as $group)
                                <option value="{{ $group->id }}"
                                    data-category-id="{{ $group->category_id }}"
                                    data-category-name="{{ $group->category->name ?? '' }}"
                                    data-brand-id="{{ $group->brand_id }}"
                                    data-brand-name="{{ $group->brand->name ?? '' }}"
                                    data-product-type="{{ $group->product_type }}"
                                    @selected(old('product_group_id') == $group->id)>{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary" id="addGroupBtn">+</button>
                        </div>
                        @error('product_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">
                            <a href="{{ route('admin.products.index') }}">Quản lý sản phẩm</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nhập tên sản phẩm">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loại sản phẩm <span class="text-danger">*</span></label>
                        <select name="product_type_disabled" id="productType" class="form-select" disabled>
                            <option value="quantity" @selected(old('product_type', 'quantity') == 'quantity')>Theo số lượng</option>
                            <option value="imei/serial" @selected(old('product_type') == 'imei/serial')>Theo IMEI/Serial</option>
                        </select>
                        <input type="hidden" name="product_type" id="productTypeHidden" value="{{ old('product_type', 'quantity') }}">
                        <div class="form-text">
                            <span id="typeHint">Nhập số lượng tồn kho cho từng biến thể.</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select id="categorySelect" class="form-select @error('category_id') is-invalid @enderror" disabled>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Thương hiệu <span class="text-danger">*</span></label>
                            <select id="brandSelect" class="form-select @error('brand_id') is-invalid @enderror" disabled>
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-0 mt-3">
                        <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                        <textarea name="description" rows="4"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Nhập mô tả sản phẩm">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Giá cơ bản (đ)</label>
                                <input type="number" name="price" id="productPrice" value="{{ old('price', 0) }}"
                                    class="form-control @error('price') is-invalid @enderror" min="0" step="0.01">
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 d-none" id="productStorageWrapper">
                                <label class="form-label">Dung lượng <span class="text-danger">*</span></label>
                                <input type="text" name="storage" id="productStorage" value="{{ old('storage') }}"
                                    class="form-control @error('storage') is-invalid @enderror" placeholder="VD: 128GB">
                                @error('storage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    
                </div>
            </section>

            {{-- Biến thể --}}
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Biến thể sản phẩm <span class="text-danger">*</span></h5>
                        <div class="text-muted small">Bắt buộc có ít nhất một biến thể.</div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addVariant">
                        <i class="bi bi-plus-lg"></i> Thêm biến thể
                    </button>
                </div>

                <div class="p-3" id="variantsContainer">
                    <p class="text-muted small mb-0" id="noVariantMsg">Chưa có biến thể nào.</p>
                </div>

                @error('variants')
                <div class="px-3 pb-3">
                    <div class="alert alert-danger py-2 mb-0">{{ $message }}</div>
                </div>
                @enderror
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel">
                <div class="panel-header">
                    <div>
<h5 class="mb-1">Ảnh và trạng thái</h5>
                    </div>
                </div>

                <div class="p-3">
                    <div class="mb-3">
                        <label class="form-label">Ảnh đại diện</label>
                        <input type="file" name="thumbnail" accept="image/*" id="thumbnailInput"
                            class="form-control @error('thumbnail') is-invalid @enderror">
                        @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <img id="thumbnailPreview" src="#" class="img-fluid mt-2 rounded border d-none" style="max-height:220px;object-fit:cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ảnh bổ sung</label>
                        <input type="file" name="images[]" accept="image/*" multiple class="form-control">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked(old('status', true))>
                            <label class="form-check-label" for="status">Đang bán</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save"></i> Lưu sản phẩm
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">Hủy</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
let variantIndex = 0;
const productTypeSelect = document.getElementById('productType');
const productTypeInput  = document.getElementById('productTypeHidden');
const productGroupSelect = document.getElementById('productGroupSelect');
const categorySelect = document.getElementById('categorySelect');
const brandSelect = document.getElementById('brandSelect');
const typeHint          = document.getElementById('typeHint');
let productGroupTomSelect = null;

function getProductType() {
    return (productTypeInput && productTypeInput.value) || (productTypeSelect && productTypeSelect.value) || 'quantity';
}

function syncGroupFields() {
    const option = productGroupSelect && productGroupSelect.options[productGroupSelect.selectedIndex];
    if (!option || !option.value) return;

    const productType = option.dataset.productType || 'quantity';
    if (productTypeSelect) productTypeSelect.value = productType;
    if (productTypeInput) productTypeInput.value = productType;
    if (categorySelect) categorySelect.value = option.dataset.categoryId || '';
    if (brandSelect) brandSelect.value = option.dataset.brandId || '';

    updateTypeHint();
    updateStorageVisibility();
}

function initProductGroupSearch() {
    if (!productGroupSelect || !window.TomSelect) return;

    const productGroupMeta = {};
    Array.from(productGroupSelect.options).forEach(option => {
        if (!option.value) return;

        productGroupMeta[option.value] = {
            category: option.dataset.categoryName || '',
            brand: option.dataset.brandName || '',
            type: option.dataset.productType === 'imei/serial' ? 'IMEI/Serial' : 'Theo so luong'
        };
    });

    productGroupTomSelect = new TomSelect(productGroupSelect, {
        allowEmptyOption: true,
        plugins: ['clear_button'],
        placeholder: 'Tìm dòng sản phẩm...',
        searchField: ['text', 'category', 'brand', 'type'],
        render: {
            option: function(data, escape) {
                if (!data.value) return '<div class="text-muted">' + escape(data.text) + '</div>';
                const meta = [data.brand, data.category, data.type].filter(Boolean).join(' · ');
                return '<div><div class="fw-semibold">' + escape(data.text) + '</div><div class="small text-muted">' + escape(meta) + '</div></div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        },
        onInitialize: function() {
            Object.entries(productGroupMeta).forEach(([value, meta]) => {
                if (!this.options[value]) return;
                this.updateOption(value, Object.assign({}, this.options[value], meta));
            });
            this.refreshOptions(false);
        }
    });
}

function hasStorage() {
    return getProductType() === 'imei/serial';
}

function stockInputHtml(index) {
    if (getProductType() === 'imei/serial') {
        return `<div class="col-12 stock-col">
            <label class="form-label small">Danh sách IMEI / Serial <span class="text-danger">*</span></label>
            <textarea name="variants[${index}][imeis]" rows="5"
                class="form-control form-control-sm"
                placeholder="Mỗi dòng một IMEI hoặc Serial&#10;123456789012345&#10;123456789012346&#10;123456789012347"></textarea>
            <div class="form-text">Mỗi dòng nhập một IMEI hoặc Serial.</div>
            <input type="hidden" name="variants[${index}][stock_quantity]" value="0">
        </div>`;
    }
return `<div class="col-md-2 stock-col">
        <label class="form-label small">Số lượng</label>
        <input type="number" name="variants[${index}][stock_quantity]"
            class="form-control form-control-sm" value="0" min="0">
    </div>`;
}

function updateTypeHint() {
    typeHint.textContent = getProductType() === 'imei/serial'
        ? 'Tồn kho tự động tính từ số IMEI đã nhập, không cần nhập tay.'
        : 'Nhập số lượng tồn kho cho từng biến thể.';

    document.querySelectorAll('.variant-row').forEach(row => {
        const stockCol = row.querySelector('.stock-col');
        if (stockCol) {
            const tmp = document.createElement('div');
            tmp.innerHTML = stockInputHtml(row.dataset.index);
            stockCol.replaceWith(tmp.firstElementChild);
        }
    });
}
function updateStorageVisibility() {
    const wrapper = document.getElementById('productStorageWrapper');
    if (hasStorage()) {
        wrapper.classList.remove('d-none');
    } else {
        wrapper.classList.add('d-none');
        // clear value when hidden
        const input = document.getElementById('productStorage');
        if (input) input.value = '';
    }
}

initProductGroupSearch();
productGroupSelect && productGroupSelect.addEventListener('change', syncGroupFields);
syncGroupFields();
updateTypeHint();
updateStorageVisibility();

document.getElementById('addVariant').addEventListener('click', function () {
    document.getElementById('noVariantMsg').classList.add('d-none');
    const container = document.getElementById('variantsContainer');
    const div = document.createElement('div');
    div.className = 'border rounded p-3 mb-2 variant-row';
    div.dataset.index = variantIndex;

    div.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-md-3 color-col">
                <label class="form-label small">Màu <span class="text-danger">*</span></label>
                <input type="text" name="variants[${variantIndex}][color]"
                    class="form-control form-control-sm" placeholder="VD: Đen">
            </div>
            <div class="col-md-4 image-col">
                <label class="form-label small">Ảnh biến thể (chọn được nhiều ảnh)</label>
                <input type="file" name="variants[${variantIndex}][images][]"
                    class="form-control form-control-sm" accept="image/*" multiple>
            </div>
            
            ${stockInputHtml(variantIndex)}
            <div class="col-md-3">
                <label class="form-label small">Giá cộng thêm (đ)</label>
                <input type="number" name="variants[${variantIndex}][additional_price]"
                    class="form-control form-control-sm" value="0" min="0">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-variant">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>`;

    container.appendChild(div);
    variantIndex++;

    div.querySelector('.remove-variant').addEventListener('click', function () {
        div.remove();
        if (!container.querySelectorAll('.variant-row').length) {
            document.getElementById('noVariantMsg').classList.remove('d-none');
        }
    });
});

document.getElementById('productForm').addEventListener('submit', function (e) {
    if (!document.querySelectorAll('.variant-row').length) {
        e.preventDefault();
        alert('Vui lòng thêm ít nhất một biến thể sản phẩm.');
        document.getElementById('addVariant').scrollIntoView({ behavior: 'smooth' });
    }
});

document.getElementById('thumbnailInput').addEventListener('change', function () {
    const preview = document.getElementById('thumbnailPreview');
    if (this.files[0]) {
        preview.src = URL.createObjectURL(this.files[0]);
        preview.classList.remove('d-none');
    }
});

// Quick-create Product Group modal handling
const addGroupBtn = document.getElementById('addGroupBtn');
const groupModalEl = document.getElementById('groupModal');
let groupModal = null;
if (groupModalEl && window.bootstrap) {
        groupModal = new bootstrap.Modal(groupModalEl);
}

addGroupBtn && addGroupBtn.addEventListener('click', function () {
        if (groupModal) groupModal.show();
});

const groupForm = document.getElementById('groupForm');
if (groupForm) {
        groupForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const url = '{{ route('admin.products.ajaxStore') }}';
                const fd = new FormData(groupForm);
                fetch(url, {
                        method: 'POST',
                        headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                }).then(r => r.json())
                .then(data => {
                        // append new option and select it
                        const sel = document.getElementById('productGroupSelect');
                        const opt = document.createElement('option');
                        opt.value = data.id;
                        opt.textContent = data.name;
                        opt.dataset.categoryId = data.category_id || '';
                        opt.dataset.categoryName = data.category ? data.category.name : '';
                        opt.dataset.brandId = data.brand_id || '';
                        opt.dataset.brandName = data.brand ? data.brand.name : '';
                        opt.dataset.productType = data.product_type || 'quantity';
                        sel.appendChild(opt);
                        if (productGroupTomSelect) {
                                productGroupTomSelect.addOption({
                                        value: String(data.id),
                                        text: data.name,
                                        category: data.category ? data.category.name : '',
                                        brand: data.brand ? data.brand.name : '',
                                        type: data.product_type === 'imei/serial' ? 'IMEI/Serial' : 'Theo so luong'
                                });
                                productGroupTomSelect.setValue(String(data.id), true);
                                productGroupTomSelect.refreshOptions(false);
                        } else {
                                sel.value = data.id;
                        }
                        syncGroupFields();
                        if (groupModal) groupModal.hide();
                        groupForm.reset();
                }).catch(err => {
                        console.error(err);
                        alert('Dòng Sản Phẩm Đã Tồn Tại.');
                });
        });
}
</script>
@endpush

<!-- Modal: Create Product Group -->
<div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="groupForm">
                <div class="modal-header">
                    <h5 class="modal-title">Tạo dòng sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên dòng sản phẩm</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thương hiệu</label>
                        <select name="brand_id" class="form-select" required>
                            <option value="">-- Chọn thương hiệu --</option>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại quản lý</label>
                        <select name="product_type" class="form-select" required>
                            <option value="quantity">Theo số lượng</option>
                            <option value="imei/serial">Theo IMEI/Serial</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary btn-sm">Tạo</button>
                </div>
            </form>
        </div>
    </div>
</div>
