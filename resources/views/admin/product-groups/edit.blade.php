@extends('layouts.admin')

@section('title', 'Sửa dòng sản phẩm')
@section('page_icon', 'bi-pencil-square')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sửa dòng sản phẩm')
@section('page_subtitle', 'Cập nhật tên dòng/model, danh mục và thương hiệu.')

@section('heading_actions')
<a href="{{ route('admin.product-groups.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">{{ $productGroup->name }}</h5>
            <div class="text-muted small">Thay đổi này sẽ ảnh hưởng các sản phẩm đang thuộc dòng này.</div>
        </div>
    </div>

    <div class="p-3">
        <form action="{{ route('admin.product-groups.update', $productGroup) }}" method="POST" style="max-width: 760px;">
            @csrf
            @method('PUT')

            @include('admin.product-groups.partials.form', ['productGroup' => $productGroup])

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save"></i> Lưu thay đổi
                </button>
                <a href="{{ route('admin.product-groups.index') }}" class="btn btn-light btn-sm">Hủy</a>
            </div>
        </form>
    </div>
</section>
@endsection
