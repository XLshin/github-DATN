@php
    $editingGroup = $productGroup ?? null;
    $hasLockedInventory = $editingGroup && $editingGroup->products()
        ->whereHas('variants', fn ($query) => $query
            ->where('stock_quantity', '>', 0)
            ->orWhereHas('imeis'))
        ->exists();
    $includeExtraSections = $includeExtraSections ?? true;
@endphp

<div class="mb-3">
    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
    <input type="text"
        name="name"
        value="{{ old('name', $editingGroup->name ?? '') }}"
        class="form-control @error('name') is-invalid @enderror"
        placeholder="VD: iPhone 17 Pro Max">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">-- Chọn danh mục --</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $editingGroup->category_id ?? '') == $category->id)>
                {{ $category->name }}
            </option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Thương hiệu <span class="text-danger">*</span></label>
        <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
            <option value="">-- Chọn thương hiệu --</option>
            @foreach($brands as $brand)
            <option value="{{ $brand->id }}" @selected(old('brand_id', $editingGroup->brand_id ?? '') == $brand->id)>
                {{ $brand->name }}
            </option>
            @endforeach
        </select>
        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3 mt-3">
    <label class="form-label">Loại quản lý <span class="text-danger">*</span></label>
    <select name="product_type"
        class="form-select @error('product_type') is-invalid @enderror"
        required
        @disabled($hasLockedInventory)>
        <option value="imei/serial" @selected(old('product_type', $editingGroup->product_type ?? 'imei/serial') === 'imei/serial')>Theo IMEI/Serial</option>
        <option value="quantity" @selected(old('product_type', $editingGroup->product_type ?? 'imei/serial') === 'quantity')>Theo số lượng</option>
    </select>
    @if($hasLockedInventory)
    <input type="hidden" name="product_type" value="{{ $editingGroup->product_type }}">
    <div class="form-text">Không thể đổi loại quản lý vì sản phẩm đã có IMEI hoặc tồn kho.</div>
    @else
    <div class="form-text">Có thể đổi loại quản lý khi sản phẩm chưa có IMEI và chưa có tồn kho.</div>
    @endif
    @error('product_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Ảnh đại diện sản phẩm</label>
        <input type="file"
            name="product_thumbnail"
            class="form-control @error('product_thumbnail') is-invalid @enderror"
            accept="image/*">
        @error('product_thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Ảnh bổ sung sản phẩm</label>
        <input type="file"
            name="product_images[]"
            class="form-control @error('product_images.*') is-invalid @enderror"
            accept="image/*"
            multiple>
        @error('product_images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

@if($editingGroup && $editingGroup->images->isNotEmpty())
<div class="mb-3">
    <label class="form-label">Ảnh sản phẩm hiện có</label>
    <div class="row g-2">
        @foreach($editingGroup->images as $image)
        <div class="col-6 col-md-3 col-xl-2">
            <div class="position-relative">
                <img src="{{ Storage::url($image->image_path) }}"
                    alt="{{ $editingGroup->name }}"
                    class="img-fluid rounded border"
                    style="height: 110px; width: 100%; object-fit: cover;">
                <button type="submit"
                    form="delete-product-group-image-{{ $image->id }}"
                    class="btn btn-sm btn-danger position-absolute top-0 end-0"
                    title="Xóa ảnh"
                    onclick="return confirm('Bạn có chắc muốn xóa ảnh này không? File ảnh trong storage cũng sẽ bị xóa.')">
                    &times;
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="form-text">Ảnh đầu tiên thường được dùng làm ảnh đại diện khi hiển thị danh sách.</div>
</div>
@endif

<div class="mb-3">
    <label class="form-label">Mô tả</label>
    <textarea name="description"
        rows="4"
        class="form-control @error('description') is-invalid @enderror"
        placeholder="Mô tả chung cho sản phẩm">{{ old('description', $editingGroup->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@if($includeExtraSections)
    @include('admin.products.partials.specifications-form', ['productGroup' => $editingGroup])

    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Trạng thái hiển thị sản phẩm</div>
            <div class="small">Tắt trạng thái này sẽ ẩn sản phẩm khỏi các nơi hiển thị chính. Admin vẫn có thể xem và sửa lại sau.</div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
                @checked(old('status', $editingGroup->status ?? true))>
            <label class="form-check-label" for="status">Hiển thị sản phẩm</label>
        </div>
    </div>
@endif
