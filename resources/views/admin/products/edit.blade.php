@extends('admin.layouts.app')

@section('title', 'Sửa sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square"></i> Sửa sản phẩm: {{ $product->name }}</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thương hiệu <span class="text-danger">*</span></label>
                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá (đ) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price', $product->price) }}" min="0">
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tồn kho <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror"
                                   value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                            @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ảnh bổ sung --}}
            @if($product->images->count())
            <div class="card shadow-sm mb-3">
                <div class="card-header"><strong>Ảnh sản phẩm hiện tại</strong></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @foreach($product->images as $img)
                    <div class="position-relative">
                        <img src="{{ Storage::url($img->image_path) }}" width="80" height="80" style="object-fit:cover" class="rounded border">
                        <form action="{{ route('admin.products.image.destroy', $img) }}" method="POST"
                              onsubmit="return confirm('Xóa ảnh này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 px-1" style="font-size:10px">x</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Ảnh đại diện</label>
                        @if($product->thumbnail)
                            <img src="{{ Storage::url($product->thumbnail) }}" class="img-fluid rounded mb-2" style="max-height:150px">
                        @endif
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thêm ảnh bổ sung</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
                                   {{ old('status', $product->status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Đang bán</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Cập nhật
                    </button>
                </div>
            </div>

            {{-- Variants --}}
            <div class="card shadow-sm">
                <div class="card-header"><strong>Biến thể ({{ $product->variants->count() }})</strong></div>
                <div class="card-body p-2">
                    @forelse($product->variants as $v)
                    <div class="border rounded p-2 mb-1 small">
                        <span class="badge bg-secondary">{{ $v->color }}</span>
                        <span class="badge bg-info text-dark">{{ $v->storage }}</span>
                        Tồn: {{ $v->stock_quantity }} | +{{ number_format($v->additional_price) }}đ
                    </div>
                    @empty
                    <p class="text-muted small mb-0">Không có biến thể.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
