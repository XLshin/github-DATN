@php
    $editingGroup = $productGroup ?? null;
    $hasProducts = $editingGroup && ($editingGroup->products_count ?? $editingGroup->products()->count()) > 0;
@endphp

<div class="mb-3">
    <label class="form-label">Tên dòng sản phẩm <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $editingGroup->name ?? '') }}"
        class="form-control @error('name') is-invalid @enderror"
        placeholder="VD: iPhone 15 Pro Max">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">-- Chọn danh mục --</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $editingGroup->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Thương hiệu <span class="text-danger">*</span></label>
        <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
            <option value="">-- Chọn thương hiệu --</option>
            @foreach($brands as $brand)
            <option value="{{ $brand->id }}" @selected(old('brand_id', $editingGroup->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3 mt-3">
    <label class="form-label">Loại quản lý <span class="text-danger">*</span></label>
    <select name="product_type" class="form-select @error('product_type') is-invalid @enderror" @disabled($hasProducts)>
        <option value="quantity" @selected(old('product_type', $editingGroup->product_type ?? 'quantity') === 'quantity')>Theo số lượng</option>
        <option value="imei/serial" @selected(old('product_type', $editingGroup->product_type ?? 'quantity') === 'imei/serial')>Theo IMEI/Serial</option>
    </select>
    @if($hasProducts)
    <input type="hidden" name="product_type" value="{{ $editingGroup->product_type }}">
    <div class="form-text">Không thể đổi loại quản lý vì dòng sản phẩm này đã tồn tại.</div>
    @endif
    @error('product_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Mô tả</label>
    <textarea name="description" rows="4"
        class="form-control @error('description') is-invalid @enderror"
        placeholder="Ghi chú nội bộ hoặc mô tả ngắn cho dòng sản phẩm">{{ old('description', $editingGroup->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@include('admin.products.partials.specifications-form', ['productGroup' => $editingGroup])

<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
            @checked(old('status', $editingGroup->status ?? true))>
        <label class="form-check-label" for="status">Đang dùng</label>
    </div>
</div>
