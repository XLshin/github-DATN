@extends('layouts.admin')

@section('title', $brand->name)
@section('page_icon', 'bi-award')
@section('page_eyebrow', 'Thương hiệu')
@section('page_title', $brand->name)
@section('page_subtitle', $brand->description ?? 'Danh sách sản phẩm của thương hiệu')

@section('heading_actions')
    <a href="{{ route('admin.brands.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil"></i> Sửa thương hiệu
    </a>
@endsection

@section('content')

{{-- Thông tin thương hiệu --}}
<div class="row g-3 mb-4">
    <div class="col-md-auto">
        <div class="panel p-3 d-flex align-items-center gap-3">
            @if($brand->logo)
                <img src="{{ asset('storage/' . $brand->logo) }}" width="64" height="64"
                     class="rounded border" style="object-fit:cover" alt="{{ $brand->name }}">
            @else
                <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                     style="width:64px;height:64px">
                    <i class="bi bi-image text-muted fs-4"></i>
                </div>
            @endif
            <div>
                <div class="fw-bold fs-5">{{ $brand->name }}</div>
                <div class="text-muted small">
                    Danh mục:
                    @if($brand->categories->isNotEmpty())
                        {{ $brand->categories->pluck('name')->join(', ') }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="panel p-3 text-center">
            <div class="fs-2 fw-bold text-primary">{{ $products->total() }}</div>
            <div class="text-muted small">Tổng sản phẩm</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="panel p-3 text-center">
            <div class="fs-2 fw-bold text-success">{{ $categories->count() }}</div>
            <div class="text-muted small">Danh mục có sản phẩm</div>
        </div>
    </div>
</div>

{{-- Lọc theo danh mục --}}
@if($categories->isNotEmpty())
<section class="panel mb-4">
    <div class="panel-header">
        <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Lọc theo danh mục</h6>
    </div>
    <div class="p-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.brands.show', $brand) }}"
               class="btn btn-sm {{ !request('category_id') ? 'btn-primary' : 'btn-outline-secondary' }}">
                Tất cả ({{ $products->total() }})
            </a>
            @foreach($categories as $category)
                <a href="{{ route('admin.brands.show', $brand) }}?category_id={{ $category->id }}"
                   class="btn btn-sm {{ request('category_id') == $category->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $category->name }}
                    <span class="badge bg-white text-dark ms-1">{{ $category->products_count }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Danh sách sản phẩm --}}
<section class="panel">
    <div class="panel-header">
        <h6 class="mb-0">
            <i class="bi bi-box-seam me-2"></i>
            Sản phẩm của {{ $brand->name }}
            @if(request('category_id'))
                — {{ $categories->firstWhere('id', request('category_id'))?->name }}
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
                    <th>Danh mục</th>
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
                        <td>{{ $product->category->name ?? '-' }}</td>
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
                            Chưa có sản phẩm nào
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
