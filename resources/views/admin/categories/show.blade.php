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
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Thương hiệu</th>
                    <th>Biến thể</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->thumbnail)
                                <img src="{{ asset('storage/' . $product->thumbnail) }}"
                                     width="52" height="52" class="rounded border" style="object-fit:cover" alt="">
                            @elseif($product->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                     width="52" height="52" class="rounded border" style="object-fit:cover" alt="">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                     style="width:52px;height:52px">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $product->brand?->name ?? '—' }}
                            </span>
                        </td>
                        <td>
                            @if($product->variants->isNotEmpty())
                                @php $first = $product->variants->first(); @endphp
                                <span
                                    tabindex="0"
                                    data-bs-toggle="popover"
                                    data-bs-trigger="hover focus"
                                    data-bs-placement="bottom"
                                    data-bs-html="true"
                                    data-bs-content="{{ $product->variants->map(fn($v) => '<span class=\'badge bg-secondary-subtle text-secondary border me-1 mb-1\'>' . e($v->color) . ' / ' . e($v->storage) . '</span>')->implode('') }}"
                                    class="badge bg-secondary-subtle text-secondary border"
                                    style="font-size:11px;cursor:pointer">
                                    {{ $first->color }} / {{ $first->storage }}
                                    @if($product->variants->count() > 1)
                                        <span class="ms-1 text-muted">+{{ $product->variants->count() - 1 }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted small">Chưa có</span>
                            @endif
                        </td>
                        <td>{{ number_format($product->price, 0, ',', '.') }}đ</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>
                            @if($product->status)
                                <span class="badge bg-success-subtle text-success">Đang bán</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Ẩn</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-light btn-sm">Sửa</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
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

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });
</script>
@endpush

@endsection
