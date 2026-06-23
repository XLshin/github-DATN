@extends('layouts.admin')

@section('title', 'Sửa thương hiệu')
@section('page_icon', 'bi-tags')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sửa thương hiệu')
@section('page_subtitle', 'Cập nhật thông tin thương hiệu, logo và mô tả.')

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
                Chỉnh sửa tên, logo và mô tả của thương hiệu.
            </div>
        </div>
    </div>

    <div class="p-3">
        <form
            action="{{ route('brands.update', $brand) }}"
            method="POST"
            enctype="multipart/form-data"
            style="max-width: 700px;">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">
                    Tên thương hiệu <span class="text-danger">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $brand->name) }}"
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

                @if($brand->logo)
                <div class="mb-2">
                    <img
                        src="{{ asset('storage/' . $brand->logo) }}"
                        alt="{{ $brand->name }}"
                        width="90"
                        height="90"
                        class="rounded border"
                        style="object-fit: cover;">
                </div>
                @else
                <div class="text-muted small mb-2">
                    Chưa có logo
                </div>
                @endif

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
                    placeholder="Nhập mô tả thương hiệu">{{ old('description', $brand->description) }}</textarea>

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

                <a href="{{ route('brands.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection