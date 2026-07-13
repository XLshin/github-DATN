@extends('layouts.admin')

@section('title', 'Sửa danh mục')
@section('page_icon', 'bi-folder')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sửa danh mục')
@section('page_subtitle', 'Cập nhật tên và mô tả danh mục sản phẩm.')

@section('heading_actions')
<a href="{{ route('admin.categories.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin danh mục</h5>
            <div class="text-muted small">
                Chỉnh sửa thông tin danh mục sản phẩm.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" style="max-width: 700px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">
                    Tên danh mục <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $category->name) }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Nhập tên danh mục">

                @error('name')
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
                    placeholder="Nhập mô tả danh mục">{{ old('description', $category->description) }}</textarea>

                @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Cập nhật
                </button>

                <a href="{{ route('admin.categories.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection