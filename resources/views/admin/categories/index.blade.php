@extends('layouts.admin')

@section('title', 'Danh mục')
@section('page_icon', 'bi-tags')
@section('page_eyebrow', 'Sản phẩm')
@section('page_title', 'Danh sách danh mục')
@section('page_subtitle', 'Quản lý danh mục sản phẩm.')

@section('heading_actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Thêm danh mục</a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="d-flex gap-2 flex-grow-1">
                <input type="text" name="search" class="form-control form-control-sm table-search" placeholder="Tìm kiếm..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary btn-sm">Tìm</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Tên</th><th>Mô tả</th><th class="text-end">Thao tác</th></tr></thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 60) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i> Xem</a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-light btn-sm">Sửa</a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa danh mục?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Chưa có danh mục</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())<div class="p-3">{{ $categories->links() }}</div>@endif
    </section>
@endsection
