@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')
@section('page_icon', 'bi-pencil-square')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sửa sản phẩm')
@section('page_subtitle', 'Cập nhật thông tin sản phẩm, hình ảnh, trạng thái và thêm biến thể mới nếu cần.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    @method('PUT')

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
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nhập tên sản phẩm">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loại sản phẩm <span class="text-danger">*</span></label>
                        <select name="product_type" id="productType" class="form-select">
                            <option value="quantity" @selected(old('product_type', $product->product_type) == 'quantity')>Theo số lượng</option>
                            <option value="imei/serial" @selected(old('product_type', $product->product_type) == 'imei/serial')>Theo IMEI/Serial</option>
                        </select>
                        <div class="form-text">
                            <span id="typeHint">Nhập số lượng tồn kho cho từng biến thể.</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tổng tồn kho <span class="text-danger">*</span></label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}"
                            class="form-control @error('stock_quantity') is-invalid @enderror" min="0">
                        @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
<div class="col-md-6">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" id="categorySelect" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    data-has-storage="{{ strtolower($cat->name) !== 'phụ kiện' ? '1' : '0' }}"
                                    @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Thương hiệu <span class="text-danger">*</span></label>
                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-0 mt-3">
                        <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                        <textarea name="description" rows="4"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Nhập mô tả sản phẩm">{{ old('description', $product->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            {{-- Biến thể --}}
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Biến thể sản phẩm</h5>
                        <div class="text-muted small">Thêm biến thể mới nếu cần.</div>
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

                    @if($product->images->count())
                    <div class="mb-3">
                        <label class="form-label">Ảnh bổ sung hiện có</label>
                        <div class="row g-2">
                            @foreach($product->images as $img)
                            <div class="col-4">
                                <div class="position-relative">
                                    <img src="{{ Storage::url($img->image_path) }}" class="img-fluid rounded border" style="object-fit:cover; max-height:120px; width:100%;">
                                    <form method="POST" action="{{ route('admin.products.image.destroy', $img) }}" class="position-absolute top-0 end-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa ảnh">×</button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked(old('status', $product->status))>
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

@push('scripts')
<script>
let variantIndex = 0;
const productTypeSelect = document.getElementById('productType');
const categorySelect    = document.getElementById('categorySelect');
const typeHint          = document.getElementById('typeHint');

function getProductType() { return productTypeSelect.value; }

function hasStorage() {
    const opt = categorySelect.options[categorySelect.selectedIndex];
    return !opt || opt.dataset.hasStorage !== '0';
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
        <label class="form-label small">Tồn kho</label>
        <input type="number" name="variants[${index}][stock_quantity]"
            class="form-control form-control-sm" value="0" min="0">
    </div>`;
}

function storageColHtml(index) {
    return `<div class="col-md-2 storage-col">
        <label class="form-label small">Bộ nhớ</label>
        <input type="text" name="variants[${index}][storage]"
            class="form-control form-control-sm" placeholder="VD: 128GB">
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

function updateStorageCols() {
    document.querySelectorAll('.variant-row').forEach(row => {
const storageCol = row.querySelector('.storage-col');
        if (hasStorage()) {
            if (!storageCol) {
                const colorCol = row.querySelector('.color-col');
                const tmp = document.createElement('div');
                tmp.innerHTML = storageColHtml(row.dataset.index);
                colorCol.after(tmp.firstElementChild);
            }
        } else {
            if (storageCol) storageCol.remove();
        }
    });
}

productTypeSelect.addEventListener('change', updateTypeHint);
categorySelect.addEventListener('change', updateStorageCols);
updateTypeHint();

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
                <label class="form-label small">Ảnh biến thể</label>
                <input type="file" name="variants[${variantIndex}][images][]"
                    class="form-control form-control-sm" accept="image/*" multiple>
                <div class="form-text small">Có thể chọn nhiều ảnh cho mỗi biến thể.</div>
            </div>
            ${hasStorage() ? storageColHtml(variantIndex) : ''}
            ${stockInputHtml(variantIndex)}
            <div class="col-md-3">
                <label class="form-label small">Giá của biến thể (đ)</label>
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

document.getElementById('thumbnailInput').addEventListener('change', function () {
    const preview = document.getElementById('thumbnailPreview');
    if (this.files[0]) {
        preview.src = URL.createObjectURL(this.files[0]);
preview.classList.remove('d-none');
    }
});
</script>
@endpush