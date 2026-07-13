@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')
@section('page_icon', 'bi-pencil-square')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sửa sản phẩm')
@section('page_subtitle', 'Cập nhật thông tin sản phẩm, phiên bản, màu sắc và thông số kỹ thuật.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">{{ $productGroup->name }}</h5>
            <div class="text-muted small">Chỉnh thông tin chung trước, sau đó cập nhật phiên bản và màu sắc bên dưới.</div>
        </div>
    </div>

    <div class="p-3">
        <div class="alert alert-info d-flex gap-2 align-items-start">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                <div class="fw-semibold">Lưu ý khi sửa sản phẩm</div>
                <div class="small">
                    Tên sản phẩm nên là tên model gốc, ví dụ <strong>iPhone 17 Pro Max</strong>.
                    Các tên kèm dung lượng như <strong>iPhone 17 Pro Max 256GB</strong> nên nằm ở phần phiên bản.
                </div>
            </div>
        </div>

        <form action="{{ route('admin.products.update', $productGroup) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.product-groups.partials.form', [
                'productGroup' => $productGroup,
                'includeExtraSections' => false,
            ])

            @include('admin.product-groups.partials.versions-colors-form', ['productGroup' => $productGroup])
            @include('admin.products.partials.specifications-form', ['productGroup' => $productGroup])

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
                        @checked(old('status', $productGroup->status))>
                    <label class="form-check-label" for="status">Hiển thị sản phẩm</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">Hủy</a>
            </div>
        </form>
    </div>
</section>

@foreach($productGroup->images as $image)
<form id="delete-product-group-image-{{ $image->id }}" method="POST" action="{{ route('admin.products.image.destroy', $image) }}">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection
