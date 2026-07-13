@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Sản phẩm')
@section('page_subtitle', 'Quản lý sản phẩm, phiên bản và màu sắc theo mô hình mới.')

@section('heading_actions')
<a href="{{ route('admin.imeis.create') }}" class="btn btn-light btn-sm">
    <i class="bi bi-upc-scan"></i> Nhập IMEI/Serial
</a>
<a href="{{ route('admin.inventory.create') }}" class="btn btn-light btn-sm">
    <i class="bi bi-box-arrow-in-down"></i> Nhập kho phụ kiện
</a>
<a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm sản phẩm mới
</a>
@endsection

@push('styles')
<style>
    .product-filter-bar {
        display: grid;
        grid-template-columns: minmax(260px, 1.35fr) repeat(4, minmax(145px, 1fr)) auto;
        gap: .75rem;
        align-items: end;
    }

    .product-search {
        position: relative;
    }

    .product-search .bi {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .product-search .form-control {
        padding-left: 2.4rem;
    }

    .product-filter-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
        min-width: 150px;
    }

    .product-thumb {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: .65rem;
        border: 1px solid rgba(148, 163, 184, .25);
        background: #fff;
    }

    .version-wrap {
        background: #f8fafc;
        border-top: 1px solid rgba(148, 163, 184, .28);
        border-bottom: 1px solid rgba(148, 163, 184, .28);
        padding: .75rem 1.25rem;
    }

    .version-table {
        --bs-table-bg: transparent;
        margin-bottom: 0;
    }

    .version-table th {
        color: #64748b;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .color-list {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        max-width: 320px;
    }

    .color-chip {
        display: inline-flex;
        align-items: center;
        max-width: 120px;
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

    .color-more-chip {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .42);
        color: #475569;
    }

    @media (max-width: 1200px) {
        .product-filter-bar {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .product-filter-bar {
            grid-template-columns: 1fr;
        }

        .product-filter-actions {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="panel">
    <form method="GET" class="p-3">
        <div class="product-filter-bar">
            <div>
                <label class="form-label">Tìm kiếm</label>
                <div class="product-search">
                    <i class="bi bi-search"></i>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Tìm theo tên sản phẩm">
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

            <div>
                <label class="form-label">Loại quản lý</label>
                <select name="product_type" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="imei/serial" @selected(request('product_type') === 'imei/serial')>IMEI/Serial</option>
                    <option value="quantity" @selected(request('product_type') === 'quantity')>Theo số lượng</option>
                </select>
            </div>

            <div>
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="1" @selected(request('status') === '1')>Hiển thị</option>
                    <option value="0" @selected(request('status') === '0')>Ẩn</option>
                </select>
            </div>

            <div class="product-filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light">Xóa</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>Loại quản lý</th>
                    <th class="text-end">Phiên bản</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productGroups as $group)
                @php
                    $groupImage = $group->images->first()?->image_path
                        ?? $group->products->firstWhere('thumbnail', '!=', null)?->thumbnail;
                @endphp

                <tr>
                    <td>{{ $group->id }}</td>
                    <td>
                        @if($groupImage)
                        <img src="{{ Storage::url($groupImage) }}" alt="{{ $group->name }}" class="product-thumb">
                        @else
                        <div class="product-thumb d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-image"></i>
                        </div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.products.show', $group) }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $group->name }}
                        </a>
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
                        <span class="badge text-bg-success">Hiển thị</span>
                        @else
                        <span class="badge text-bg-secondary">Ẩn</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if($group->products_count > 0)
                            <button type="button"
                                class="btn btn-light btn-sm toggle-product-versions"
                                data-target="product-versions-{{ $group->id }}"
                                title="Xem phiên bản">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            @endif
                            <a href="{{ route('admin.products.show', $group) }}" class="btn btn-light btn-sm" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.products.edit', $group) }}" class="btn btn-light btn-sm" title="Sửa sản phẩm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.products.destroy', $group) }}" method="POST"
                                onsubmit="return confirm('Xóa sản phẩm này?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa sản phẩm" @disabled($group->products_count > 0)>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                @if($group->products_count > 0)
                <tr id="product-versions-{{ $group->id }}" class="product-versions-row d-none">
                    <td colspan="9" class="p-0">
                        <div class="version-wrap">
                            <table class="table table-sm align-middle version-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên phiên bản</th>
                                        <th>Dung lượng / phiên bản</th>
                                        <th>Màu</th>
                                        <th class="text-end">Giá base</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group->products as $product)
                                    <tr>
                                        <td class="fw-semibold">#{{ $product->id }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $product->name }}</span>
                                            <div class="text-muted small">{{ $product->variants_count }} màu</div>
                                        </td>
                                        <td>{{ $product->storage ?: 'Không có' }}</td>
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
                                                <span class="color-chip">{{ $variant->color ?: 'Không màu' }}</span>
                                                @endforeach

                                                @if($hiddenVariantCount > 0)
                                                <span class="color-chip color-more-chip">+{{ $hiddenVariantCount }}</span>
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
                    <td colspan="9" class="text-center text-muted py-4">Chưa có sản phẩm nào.</td>
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
document.querySelectorAll('.toggle-product-versions').forEach((button) => {
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
