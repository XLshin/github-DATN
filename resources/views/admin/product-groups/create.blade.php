@extends('layouts.admin')

@section('title', 'Thêm dòng sản phẩm')
@section('page_icon', 'bi-plus-circle')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Thêm dòng sản phẩm')
@section('page_subtitle', 'Tạo dòng/model để gom các sản phẩm cùng nhóm.')

@section('heading_actions')
<a href="{{ route('admin.product-groups.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin dòng sản phẩm</h5>
            <div class="text-muted small">Tên dòng nên là tên model gốc, ví dụ iPhone 15 Pro Max.</div>
        </div>
    </div>

    <div class="p-3">
        <form action="{{ route('admin.product-groups.store') }}" method="POST" style="max-width: 760px;">
            @csrf

            @include('admin.product-groups.partials.form', ['productGroup' => null])

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Thêm dòng sản phẩm
                </button>
                <a href="{{ route('admin.product-groups.index') }}" class="btn btn-light btn-sm">Hủy</a>
            </div>
        </form>
    </div>
</section>
@endsection
