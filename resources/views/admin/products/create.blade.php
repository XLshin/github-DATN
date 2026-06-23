@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')
@section('page_icon', 'bi-plus-circle')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm mới, thêm ảnh đại diện, ảnh bổ sung và biến thể sản phẩm.')

@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm mới, thêm ảnh đại diện, ảnh bổ sung và biến thể sản phẩm.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-3">
        <div class="col-lg-8">
            <section class="panel mb-3">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Thông tin sản phẩm</h5>
                        <div class="text-muted small">
                            Nhập tên, danh mục, thương hiệu, mô tả, giá và tồn kho.
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="mb-3">
                        <label class="form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nhập tên sản phẩm">

                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Danh mục <span class="text-danger">*</span>
                            </label>

                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Chọn danh mục --</option>

                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id')==$cat->id)>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>

                            @error('category_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Thương hiệu <span class="text-danger">*</span>
                            </label>

                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">-- Chọn thương hiệu --</option>

                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id')==$brand->id)>
                                    {{ $brand->name }}
                                </option>
                                @endforeach
                            </select>

                            @error('brand_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">
                            Mô tả <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Nhập mô tả sản phẩm">{{ old('description') }}</textarea>

                        @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Giá (đ) <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="price"
                                value="{{ old('price') }}"
                                min="0"
                                class="form-control @error('price') is-invalid @enderror"
                                placeholder="Nhập giá sản phẩm">

                            @error('price')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Tồn kho <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="stock_quantity"
                                value="{{ old('stock_quantity', 0) }}"
                                min="0"
                                class="form-control @error('stock_quantity') is-invalid @enderror"
                                placeholder="Nhập số lượng tồn kho">

                            @error('stock_quantity')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Biến thể sản phẩm</h5>
                        <div class="text-muted small">
                            Thêm màu sắc, dung lượng, tồn kho và giá cộng thêm cho từng biến thể.
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addVariant">
                        <i class="bi bi-plus-lg"></i> Thêm biến thể
                    </button>
                </div>

                <div class="p-3" id="variantsContainer">
                    <p class="text-muted small mb-0" id="noVariantMsg">
                        Chưa có biến thể nào.
                    </p>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Ảnh và trạng thái</h5>
                        <div class="text-muted small">
                            Cập nhật ảnh hiển thị và trạng thái bán hàng.
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="mb-3">
                        <label class="form-label">
                            Ảnh đại diện
                        </label>

                        <input
                            type="file"
                            name="thumbnail"
                            accept="image/*"
                            id="thumbnailInput"
                            class="form-control @error('thumbnail') is-invalid @enderror">

                        @error('thumbnail')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <img
                            id="thumbnailPreview"
                            src="#"
                            alt="Ảnh đại diện"
                            class="img-fluid mt-2 rounded border d-none"
                            style="max-height: 220px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ảnh bổ sung
                        </label>

                        <input
                            type="file"
                            name="images[]"
                            accept="image/*"
                            multiple
                            class="form-control @error('images') is-invalid @enderror">

                        @error('images')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="status"
                                id="status"
                                value="1"
                                @checked(old('status', true))>

                            <label class="form-check-label" for="status">
                                Đang bán
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save"></i> Lưu sản phẩm
                        </button>

                        <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
                            Hủy
                        </a>
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

    document.getElementById('addVariant').addEventListener('click', function() {
        document.getElementById('noVariantMsg').classList.add('d-none');

        const container = document.getElementById('variantsContainer');
        const div = document.createElement('div');

        div.className = 'border rounded p-3 mb-2 variant-row';
        div.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Màu</label>
                        <input
                            type="text"
                            name="variants[${variantIndex}][color]"
                            class="form-control form-control-sm"
                            placeholder="VD: Đen">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Bộ nhớ</label>
                        <input
                            type="text"
                            name="variants[${variantIndex}][storage]"
                            class="form-control form-control-sm"
                            placeholder="VD: 128GB">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small">Tồn kho</label>
                        <input
                            type="number"
                            name="variants[${variantIndex}][stock_quantity]"
                            class="form-control form-control-sm"
                            value="0"
                            min="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Giá thêm (đ)</label>
                        <input
                            type="number"
                            name="variants[${variantIndex}][additional_price]"
                            class="form-control form-control-sm"
                            value="0"
                            min="0">
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-variant">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            `;

        container.appendChild(div);
        variantIndex++;

        div.querySelector('.remove-variant').addEventListener('click', function() {
            div.remove();

            if (!container.querySelectorAll('.variant-row').length) {
                document.getElementById('noVariantMsg').classList.remove('d-none');
            }
        });
    });

    document.getElementById('thumbnailInput').addEventListener('change', function() {
        const preview = document.getElementById('thumbnailPreview');

        if (this.files[0]) {
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove('d-none');
        }
    });
</script>
@endpush