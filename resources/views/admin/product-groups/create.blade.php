@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')
@section('page_icon', 'bi-plus-circle')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm kèm các phiên bản và màu sắc dùng chung.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin sản phẩm</h5>
            <div class="text-muted small">Nhập thông tin chung trước, sau đó thêm phiên bản và màu sắc.</div>
        </div>
    </div>

    <div class="p-3">
        <div class="alert alert-info d-flex gap-2 align-items-start">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                <div class="fw-semibold">Lưu ý khi đặt tên sản phẩm</div>
                <div class="small">
                    Tên sản phẩm nên là tên model gốc, ví dụ <strong>iPhone 17 Pro Max</strong>.
                </div>
            </div>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('admin.product-groups.partials.form', [
                'productGroup' => null,
                'includeExtraSections' => false,
            ])

            @include('admin.product-groups.partials.versions-colors-form')
            @include('admin.products.partials.specifications-form', ['productGroup' => null])

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
                        @checked(old('status', true))>
                    <label class="form-check-label" for="status">Hiển thị sản phẩm</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Thêm sản phẩm
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">Hủy</a>
            </div>
        </form>
    </div>
</section>
@endsection
