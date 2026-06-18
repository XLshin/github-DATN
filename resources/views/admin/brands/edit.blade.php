<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa thương hiệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4" style="max-width:600px">
    <h2>Sửa thương hiệu</h2>
    <a href="{{ route('brands.index') }}" class="btn btn-secondary mb-3">← Quay lại</a>

    <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $brand->name) }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Logo</label>
            @if($brand->logo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $brand->logo) }}" width="80" alt="{{ $brand->name }}">
                </div>
            @endif
            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror"
                   accept="image/*">
            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $brand->description) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</div>
</body>
</html>
