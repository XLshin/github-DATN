@extends('layouts.admin')

@section('title', $brand->name)
@section('page_icon', 'bi-award')
@section('page_eyebrow', 'Thương hiệu')
@section('page_title', $brand->name)
@section('page_subtitle', $brand->description ?? 'Danh sách sản phẩm của thương hiệu')

@section('heading_actions')
    <a href="{{ route('brands.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
    <a href="{{ route('brands.edit', $brand) }}" class="btn btn-primary btn-sm">
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
            <a href="{{ route('brands.show', $brand) }}"
               class="btn btn-sm {{ !request('category_id') ? 'btn-primary' : 'btn-outline-secondary' }}">
                Tất cả ({{ $products->total() }})
            </a>
            @foreach($categories as $category)
                <a href="{{ route('brands.show', $brand) }}?category_id={{ $category->id }}"
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
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
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
                                {{ $product->category?->name ?? '—' }}
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
                        <td>
                            @php $actual = max(0, (int)($product->total_stock ?? $product->stock_quantity) - (int)($product->sold_quantity ?? 0)); @endphp
                            {{ $actual }}
                        </td>
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

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });
</script>
@endpush

@endsection
