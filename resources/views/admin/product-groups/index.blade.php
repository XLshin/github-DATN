@extends('layouts.admin')

@section('title', 'Dòng sản phẩm')
@section('page_icon', 'bi-collection')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Dòng sản phẩm')
@section('page_subtitle', 'Quản lý các dòng/model dùng để gom các phiên bản sản phẩm.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Sản phẩm
</a>
<a href="{{ route('admin.product-groups.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm dòng sản phẩm
</a>
@endsection

@section('content')
<section class="panel">
    <form method="GET" class="p-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control" placeholder="Tìm theo tên dòng sản phẩm">
            </div>
            <div class="col-md-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Thương hiệu</label>
                <select name="brand_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid gap-2">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('admin.product-groups.index') }}" class="btn btn-outline-secondary">Xóa</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên dòng</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>Loại quản lý</th>
                    <th class="text-end">Sản phẩm</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productGroups as $group)
                <tr>
                    <td>{{ $group->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $group->name }}</div>
                        @if($group->description)
                        <div class="text-muted small">{{ Str::limit($group->description, 80) }}</div>
                        @endif
                    </td>
                    <td>{{ $group->category->name ?? '-' }}</td>
                    <td>{{ $group->brand->name ?? '-' }}</td>
                    <td>
                        @if($group->product_type === 'imei/serial')
                        <span class="badge text-bg-warning text-dark">IMEI/Serial</span>
                        @else
                        <span class="badge text-bg-secondary">Theo số lượng</span>
                        @endif
                    </td>
                    <td class="text-end">{{ $group->products_count }}</td>
                    <td>
                        @if($group->status)
                        <span class="badge text-bg-success">Đang dùng</span>
                        @else
                        <span class="badge text-bg-secondary">Ẩn</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.product-groups.edit', $group) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-pencil"></i> Sửa
                            </a>
                            <form action="{{ route('admin.product-groups.destroy', $group) }}" method="POST"
                                onsubmit="return confirm('Xóa dòng sản phẩm này?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" @disabled($group->products_count > 0)>
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Chưa có dòng sản phẩm nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($productGroups->hasPages())
    <div class="p-3">{{ $productGroups->links() }}</div>
    @endif
</section>
@endsection
