@extends('layouts.admin')

@section('title', 'Dòng sản phẩm')
@section('page_icon', 'bi-collection')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Dòng sản phẩm')
@section('page_subtitle', 'Quản lý các dòng/model và xem nhanh những phiên bản sản phẩm bên trong.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Sản phẩm
</a>
<a href="{{ route('admin.product-groups.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm dòng sản phẩm
</a>
@endsection

@push('styles')
<style>
    .group-filter-bar {
        display: grid;
        grid-template-columns: minmax(260px, 1.4fr) repeat(2, minmax(160px, 1fr)) auto;
        gap: .75rem;
        align-items: end;
    }

    .group-search {
        position: relative;
    }

    .group-search .bi {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .group-search .form-control {
        padding-left: 2.4rem;
    }

    .group-filter-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
        min-width: 150px;
    }

    .group-product-wrap {
        background: #f8fafc;
        border-top: 1px solid rgba(148, 163, 184, .28);
        border-bottom: 1px solid rgba(148, 163, 184, .28);
        padding: .75rem 1.25rem;
    }

    .group-product-table {
        --bs-table-bg: transparent;
        margin-bottom: 0;
    }

    .group-product-table th {
        color: #64748b;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .product-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: .65rem;
        border: 1px solid rgba(148, 163, 184, .25);
        background: #fff;
    }

    .color-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        max-width: 112px;
        padding: .28rem .48rem;
        border-radius: 999px;
        background: #e2e8f0;
        color: #1f2937;
        font-size: .78rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .color-list {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        max-width: 310px;
    }

    .color-more-chip {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .42);
        color: #475569;
    }

    @media (max-width: 992px) {
        .group-filter-bar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .group-filter-actions {
            grid-column: span 2;
        }
    }

    @media (max-width: 576px) {
        .group-filter-bar {
            grid-template-columns: 1fr;
        }

        .group-filter-actions {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('content')
<section class="panel">
    <form method="GET" class="p-3">
        <div class="group-filter-bar">
            <div>
                <label class="form-label">Tìm kiếm</label>
                <div class="group-search">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control" placeholder="Tìm theo tên dòng sản phẩm">
                </div>
            </div>
            <div>
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Thương hiệu</label>
                <select name="brand_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="group-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('admin.product-groups.index') }}" class="btn btn-light">Xóa</a>
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
                    <td class="text-end fw-semibold">{{ $group->products_count }}</td>
                    <td>
                        @if($group->status)
                        <span class="badge text-bg-success">Đang dùng</span>
                        @else
                        <span class="badge text-bg-secondary">Ẩn</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if($group->products_count > 0)
                            <button type="button" class="btn btn-light btn-sm toggle-group-products"
                                data-target="group-products-{{ $group->id }}" title="Xem sản phẩm">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            @endif
                            <a href="{{ route('admin.product-groups.edit', $group) }}" class="btn btn-light btn-sm" title="Sửa dòng sản phẩm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.product-groups.destroy', $group) }}" method="POST"
                                onsubmit="return confirm('Xóa dòng sản phẩm này?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa dòng sản phẩm" @disabled($group->products_count > 0)>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                @if($group->products_count > 0)
                <tr id="group-products-{{ $group->id }}" class="group-products-row d-none">
                    <td colspan="8" class="p-0">
                        <div class="group-product-wrap">
                            <table class="table table-sm align-middle group-product-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ảnh</th>
                                        <th>Sản phẩm</th>
                                        <th>Dung lượng</th>
                                        <th>Màu</th>
                                        <th class="text-end">Giá base</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group->products as $product)
                                    <tr>
                                        <td class="fw-semibold">#{{ $product->id }}</td>
                                        <td>
                                            @if($product->thumbnail)
                                            <img src="{{ Storage::url($product->thumbnail) }}" alt="{{ $product->name }}" class="product-thumb">
                                            @else
                                            <div class="product-thumb d-flex align-items-center justify-content-center text-muted">
                                                <i class="bi bi-image"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                            <div class="text-muted small">{{ $product->variants_count }} màu/biến thể</div>
                                        </td>
                                        <td>{{ $product->storage ?: 'không có' }}</td>
                                        <td>
                                            @php
                                                $visibleVariants = $product->variants->take(3);
                                                $hiddenVariantCount = max($product->variants->count() - $visibleVariants->count(), 0);
                                                $variantColorTitle = $product->variants
                                                    ->pluck('color')
                                                    ->map(fn ($color) => $color ?: 'Không màu')
                                                    ->implode(', ');
                                            @endphp

                                            @if($product->variants->isNotEmpty())
                                            <div class="color-list" title="{{ $variantColorTitle }}">
                                                @foreach($visibleVariants as $variant)
                                                <span class="color-chip">
                                                    {{ $variant->color ?: 'Không màu' }}
                                                </span>
                                                @endforeach

                                                @if($hiddenVariantCount > 0)
                                                <span class="color-chip color-more-chip">
                                                    +{{ $hiddenVariantCount }}
                                                </span>
                                                @endif
                                            </div>
                                            @else
                                            <span class="text-muted small">Chưa có màu</span>
                                            @endif
                                        </td>
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
                                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-light btn-sm" title="Xem chi tiết">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-light btn-sm" title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif
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

@push('scripts')
<script>
document.querySelectorAll('.toggle-group-products').forEach((button) => {
    button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.target);
        const icon = button.querySelector('i');
        const isHidden = target.classList.contains('d-none');

        target.classList.toggle('d-none', !isHidden);
        icon.classList.toggle('bi-chevron-down', !isHidden);
        icon.classList.toggle('bi-chevron-up', isHidden);
    });
});
</script>
@endpush
