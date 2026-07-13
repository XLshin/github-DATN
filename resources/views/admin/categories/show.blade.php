@extends('layouts.admin')

@section('title', $category->name)
@section('page_icon', 'bi-tags')
@section('page_eyebrow', 'Danh mục')
@section('page_title', $category->name)
@section('page_subtitle', $category->description ?? 'Chi tiết danh mục')

@section('heading_actions')
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil"></i> Sửa danh mục
    </a>
@endsection

@section('content')

{{-- Thống kê brands --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="panel p-3 text-center">
            <div class="fs-2 fw-bold text-primary">{{ $brands->count() }}</div>
            <div class="text-muted small">Thương hiệu</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel p-3 text-center">
            <div class="fs-2 fw-bold text-success">{{ $products->total() }}</div>
            <div class="text-muted small">Tổng sản phẩm</div>
        </div>
    </div>
</div>

{{-- Danh sách brands trong danh mục --}}
<section class="panel mb-4">
    <div class="panel-header">
        <h6 class="mb-0"><i class="bi bi-award me-2"></i>Thương hiệu trong danh mục</h6>
    </div>
    <div class="p-3">
        @if($brands->isEmpty())
            <p class="text-muted mb-0">Chưa có thương hiệu nào trong danh mục này.</p>
        @else
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.categories.show', $category) }}"
                   class="btn btn-sm {{ !request('brand_id') ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Tất cả ({{ $products->total() }})
                </a>
                @foreach($brands as $brand)
                    <a href="{{ route('admin.categories.show', $category) }}?brand_id={{ $brand->id }}"
                       class="btn btn-sm {{ request('brand_id') == $brand->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $brand->name }}
                        <span class="badge bg-white text-dark ms-1">{{ $brand->products_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Danh sách sản phẩm --}}
<section class="panel">
    <div class="panel-header">
        <h6 class="mb-0">
            <i class="bi bi-box-seam me-2"></i>
            Sản phẩm
            @if(request('brand_id'))
                — {{ $brands->firstWhere('id', request('brand_id'))?->name }}
            @endif
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Dòng</th>
                    <th>Loại quản lý</th>
                    <th>Thương hiệu</th>
                    <th class="text-end">Giá base</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            @if($product->thumbnail)
                                <img src="{{ Storage::url($product->thumbnail) }}" alt="{{ $product->name }}"
                                     width="52" height="52" class="rounded" style="object-fit:cover;">
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="fw-semibold">
                            <a href="{{ route('admin.product-versions.show', $product) }}" class="text-decoration-none text-dark">
                                {{ $product->name }}
                            </a>
                            <div class="text-muted small">{{ $product->variants_count }} biến thể</div>
                        </td>
                        <td>{{ $product->productGroup->name ?? '-' }}</td>
                        <td>
                            @if($product->product_type === 'imei/serial')
                                <span class="badge text-bg-warning text-dark">IMEI/Serial</span>
                            @else
                                <span class="badge text-bg-secondary">Theo số lượng</span>
                            @endif
                        </td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td class="text-end fw-semibold">{{ number_format($product->price ?? 0, 0, ',', '.') }} đ</td>
                        <td>
                            @if($product->status)
                                <span class="badge text-bg-success">Đang bán</span>
                            @else
                                <span class="badge text-bg-secondary">Ẩn</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.product-versions.show', $product) }}" class="btn btn-light btn-sm" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.product-versions.edit', $product) }}" class="btn btn-light btn-sm" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Chưa có sản phẩm nào trong danh mục này
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="p-3">{{ $products->links() }}</div>
    @endif
</section>

@endsection
