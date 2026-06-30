@extends('layouts.admin')

@section('title', 'Thương hiệu')
@section('page_icon', 'bi-award')
@section('page_eyebrow', 'Sản phẩm')
@section('page_title', 'Danh sách thương hiệu')
@section('page_subtitle', 'Quản lý thương hiệu sản phẩm trong hệ thống.')

@section('heading_actions')
    <a href="{{ route('brands.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Thêm thương hiệu</a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-award"></i><span>Thương hiệu</span></h2>
                <p class="text-muted mb-0">Tổng cộng {{ $brands->total() }} thương hiệu</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Logo</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th>Mô tả</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td>{{ $brand->id }}</td>
                            <td>
                                @if($brand->logo)
                                    <img src="{{ asset('storage/'.$brand->logo) }}" width="48" height="48" class="rounded object-fit-cover" alt="">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $brand->name }}</td>
                            <td>
                                @if($brand->categories->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($brand->categories as $cat)
                                            <span class="badge bg-light text-dark border">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($brand->description, 60) }}</td>
                            <td class="text-end">
                                <a href="{{ route('brands.show', $brand) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i> Xem</a>
                                <a href="{{ route('brands.edit', $brand) }}" class="btn btn-light btn-sm">Sửa</a>
                                <form action="{{ route('brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa thương hiệu này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Chưa có thương hiệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($brands->hasPages())
            <div class="p-3">{{ $brands->links() }}</div>
        @endif
    </section>
@endsection
