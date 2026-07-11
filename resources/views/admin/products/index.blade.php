@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Danh sách sản phẩm')
@section('page_subtitle', 'Bấm mũi tên để xem biến thể, giá cộng thêm và giá bán cuối.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-collection"></i> Dòng sản phẩm
</a>
<a href="{{ route('admin.imeis.create') }}" class="btn btn-light btn-sm">
    <i class="bi bi-plus-lg"></i> Nhập IMEI/Serial
</a>
<a href="{{ route('admin.inventory.create') }}" class="btn btn-light btn-sm">
    <i class="bi bi-box-arrow-in-down"></i> Nhập kho phụ kiện
</a>
<a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
</a>
@endsection

@push('styles')
<style>
    .product-filter-bar {
        display: grid;
        grid-template-columns: minmax(260px, 1.4fr) repeat(4, minmax(150px, 1fr)) auto;
        gap: .75rem;
        align-items: end;
    }

    .product-filter-search {
        position: relative;
    }

    .product-filter-search .bi {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .product-filter-search .form-control {
        padding-left: 2.4rem;
    }

    .product-filter-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
        min-width: 150px;
    }

    .product-filter-meta {
        border-top: 1px solid rgba(148, 163, 184, .24);
    }

    @media (max-width: 1200px) {
        .product-filter-bar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .product-filter-actions {
            grid-column: span 2;
        }
    }

    @media (max-width: 576px) {
        .product-filter-bar {
            grid-template-columns: 1fr;
        }

        .product-filter-actions {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('content')
<section class="panel">
    <form method="GET">
        <div class="p-3">
            <div class="product-filter-bar">
                <div>
                    <label class="form-label">Tìm kiếm</label>
                    <div class="product-filter-search">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Tên sản phẩm, dung lượng...">
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
                        <option value="active" @selected(request('status') === 'active')>Đang bán</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Ẩn</option>
                    </select>
                </div>

                <div class="product-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i>
                        Lọc
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-light">
                        Xóa
                    </a>
                </div>
            </div>
        </div>

        @if(request()->hasAny(['search', 'category_id', 'brand_id', 'product_type', 'status']))
        <div class="product-filter-meta px-3 py-2 d-flex flex-wrap align-items-center gap-2 bg-light">
            <span class="text-muted small">Đang lọc:</span>

            @if(request('search'))
                <span class="badge text-bg-primary">Từ khóa: {{ request('search') }}</span>
            @endif
            @if(request('category_id') && $categories->firstWhere('id', request('category_id')))
                <span class="badge text-bg-secondary">Danh mục: {{ $categories->firstWhere('id', request('category_id'))->name }}</span>
            @endif
            @if(request('brand_id') && $brands->firstWhere('id', request('brand_id')))
                <span class="badge text-bg-secondary">Thương hiệu: {{ $brands->firstWhere('id', request('brand_id'))->name }}</span>
            @endif
            @if(request('product_type'))
                <span class="badge text-bg-secondary">Loại: {{ request('product_type') === 'imei/serial' ? 'IMEI/Serial' : 'Theo số lượng' }}</span>
            @endif
            @if(request('status'))
                <span class="badge text-bg-secondary">Trạng thái: {{ request('status') === 'active' ? 'Đang bán' : 'Ẩn' }}</span>
            @endif
        </div>
        @endif
    </form>

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
                    <td>{{ $product->category->name ?? '-' }}</td>
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
                            @if($product->variants_count > 0)
                            <button class="btn btn-sm btn-light toggle-variants"
                                data-target="variants-{{ $product->id }}"
                                title="Xem biến thể">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            @endif
                            <a href="{{ route('admin.product-versions.show', $product) }}" class="btn btn-light btn-sm" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.product-versions.edit', $product) }}" class="btn btn-light btn-sm" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.product-versions.destroy', $product) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                @if($product->variants_count > 0)
                <tr id="variants-{{ $product->id }}" class="variant-row d-none">
                    <td colspan="10" class="p-0">
                        <div class="bg-light border-top border-bottom px-4 py-2">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Ảnh</th>
                                        <th>Màu</th>
                                        <th class="text-end">Giá cộng thêm</th>
                                        <th class="text-end">Giá bán cuối</th>
                                        <th class="text-end">Tồn kho</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->variants as $v)
                                    @php
                                        $additionalPrice = $v->additional_price ?? 0;
                                        $finalPrice = ($product->price ?? 0) + $additionalPrice;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($v->image_path)
                                            <img src="{{ Storage::url($v->image_path) }}" alt="{{ $v->color }}"
                                                width="40" height="40" class="rounded" style="object-fit:cover;">
                                            @else
                                            <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td><span class="badge text-bg-secondary">{{ $v->color }}</span></td>
                                        <td class="text-end">
                                            {{ $additionalPrice > 0 ? '+' . number_format($additionalPrice, 0, ',', '.') : '0' }} đ
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($finalPrice, 0, ',', '.') }} đ</td>
                                        <td class="text-end">{{ $v->stock_quantity }}</td>
                                        <td>
                                            @if($v->status)
                                            <span class="badge text-bg-success">Active</span>
                                            @else
                                            <span class="badge text-bg-secondary">Ẩn</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.variants.show', $v) }}" class="btn btn-sm btn-light">
                                                <i class="bi bi-eye"></i> Xem
                                            </a>
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
                    <td colspan="10" class="text-center text-muted py-4">Chưa có sản phẩm nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="p-3">{{ $products->withQueryString()->links() }}</div>
    @endif
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-variants').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        const isHidden = target.classList.contains('d-none');

        target.classList.toggle('d-none', !isHidden);
        icon.classList.toggle('bi-chevron-down', !isHidden);
        icon.classList.toggle('bi-chevron-up', isHidden);
    });
});
</script>
@endpush
