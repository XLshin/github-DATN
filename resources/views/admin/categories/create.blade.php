@extends('layouts.admin')

@section('title', 'Thêm danh mục')
@section('page_icon', 'bi-folder-plus')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Thêm danh mục')
@section('page_subtitle', 'Tạo danh mục mới để phân loại sản phẩm trong hệ thống.')

@section('heading_actions')
<a href="{{ route('categories.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin danh mục</h5>
            <div class="text-muted small">
                Nhập tên và mô tả cho danh mục sản phẩm.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form action="{{ route('categories.store') }}" method="POST" style="max-width: 700px;">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Tên danh mục <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
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
                    placeholder="Nhập mô tả danh mục">{{ old('description') }}</textarea>

                @error('description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Thêm danh mục
                </button>

                <a href="{{ route('categories.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection