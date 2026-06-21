@extends('layouts.admin')

@section('title', 'Thêm thương hiệu')
@section('page_icon', 'bi-tags')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Thêm thương hiệu')
@section('page_subtitle', 'Tạo thương hiệu mới cho sản phẩm trong hệ thống.')

@section('heading_actions')
<a href="{{ route('brands.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin thương hiệu</h5>
            <div class="text-muted small">
                Nhập tên, logo và mô tả cho thương hiệu.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data" style="max-width: 700px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">Danh mục</label>
                <select name="category_ids[]" multiple
                        class="form-select @error('category_ids') is-invalid @enderror" size="4">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ in_array($category->id, old('category_ids', [])) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều danh mục.</div>
                @error('category_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Tên thương hiệu <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Nhập tên thương hiệu">

                @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Logo
                </label>

                <input
                    type="file"
                    name="logo"
                    accept="image/*"
                    class="form-control @error('logo') is-invalid @enderror">

                @error('logo')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Mô tả
                </label>

                <textarea
                    name="description"
                    rows="4"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Nhập mô tả thương hiệu">{{ old('description') }}</textarea>

                @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Thêm thương hiệu
                </button>

                <a href="{{ route('brands.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection