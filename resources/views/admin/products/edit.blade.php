@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')
@section('page_icon', 'bi-pencil-square')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sửa sản phẩm')
@section('page_subtitle', 'Cập nhật thông tin sản phẩm, hình ảnh, trạng thái và xem biến thể hiện có.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-8">
            <section class="panel mb-3">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Thông tin sản phẩm</h5>
                        <div class="text-muted small">
                            Cập nhật tên, danh mục, thương hiệu, mô tả, giá và tồn kho.
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
                            value="{{ old('name', $product->name) }}"
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
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>
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
                                <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>
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
                            placeholder="Nhập mô tả sản phẩm">{{ old('description', $product->description) }}</textarea>

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
                                value="{{ old('price', $product->price) }}"
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
                                value="{{ old('stock_quantity', $product->stock_quantity) }}"
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
        </div>

        <div class="col-lg-4">
            <section class="panel mb-3">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">Ảnh và trạng thái</h5>
                        <div class="text-muted small">
                            Cập nhật ảnh đại diện, thêm ảnh bổ sung và trạng thái bán hàng.
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="mb-3">
                        <label class="form-label">
                            Ảnh đại diện
                        </label>

                        @if($product->thumbnail)
                        <div class="mb-2">
                            <img
                                src="{{ Storage::url($product->thumbnail) }}"
                                alt="{{ $product->name }}"
                                class="img-fluid rounded border"
                                style="max-height: 180px; object-fit: cover;">
                        </div>
                        @else
                        <div class="text-muted small mb-2">
                            Chưa có ảnh đại diện
                        </div>
                        @endif

                        <input
                            type="file"
                            name="thumbnail"
                            accept="image/*"
                            class="form-control @error('thumbnail') is-invalid @enderror">

                        @error('thumbnail')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Thêm ảnh bổ sung
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
                                @checked(old('status', $product->status))>

                            <label class="form-check-label" for="status">
                                Đang bán
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save"></i> Cập nhật sản phẩm
                        </button>

                        <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
                            Hủy
                        </a>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h5 class="mb-1">
                            Biến thể
                        </h5>
                        <div class="text-muted small">
                            Tổng số biến thể: {{ $product->variants->count() }}
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    @forelse($product->variants as $v)
                    <div class="border rounded p-2 mb-2 small">
                        <div class="d-flex flex-wrap gap-1 mb-1">
                            <span class="badge text-bg-secondary">
                                {{ $v->color }}
                            </span>

                            <span class="badge text-bg-info">
                                {{ $v->storage }}
                            </span>
                        </div>

                        <div>
                            Tồn: <strong>{{ $v->stock_quantity }}</strong>
                            |
                            +{{ number_format($v->additional_price, 0, ',', '.') }} đ
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">
                        Không có biến thể.
                    </p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</form>

@if($product->images->count())
<section class="panel mt-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Ảnh sản phẩm hiện tại</h5>
            <div class="text-muted small">
                Xem và xóa các ảnh bổ sung đang dùng cho sản phẩm.
            </div>
        </div>
    </div>

    <div class="p-3 d-flex flex-wrap gap-2">
        @foreach($product->images as $img)
        <div class="position-relative">
            <img
                src="{{ Storage::url($img->image_path) }}"
                alt="Ảnh sản phẩm"
                width="90"
                height="90"
                class="rounded border"
                style="object-fit: cover;">

            <form
                action="{{ route('admin.products.image.destroy', $img) }}"
                method="POST"
                class="position-absolute top-0 end-0"
                onsubmit="return confirm('Xóa ảnh này?')">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger btn-sm p-0 px-1" style="font-size: 10px;">
                    x
                </button>
            </form>
        </div>
        @endforeach
    </div>
</section>
@endif
@endsection