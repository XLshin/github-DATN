@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Danh sách sản phẩm')
@section('page_subtitle', 'Bấm mũi tên để xem biến thể của từng sản phẩm.')

@section('heading_actions')
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

@section('content')
<section class="panel">
    <form method="GET" class="p-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control" placeholder="Tìm theo tên sản phẩm">
            </div>
            <div class="col-md-2">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Thương hiệu</label>
                <select name="brand_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bán</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Ẩn</option>
                </select>
            </div>
            <div class="col-md-2 d-grid gap-2">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Xóa</a>
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
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
<tbody>
                @forelse($products as $product)
                {{-- Hàng sản phẩm --}}
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
                        <a href="{{ route('admin.products.show', $product) }}" class="text-decoration-none text-dark">
                            {{ $product->name }}
                        </a>
                    </td>

                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>

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

                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-light btn-sm" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Hàng expand biến thể --}}
                @if($product->variants_count > 0)
<tr id="variants-{{ $product->id }}" class="variant-row d-none">
                    <td colspan="9" class="p-0">
                        <div class="bg-light border-top border-bottom px-4 py-2">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Màu</th>
                                        <th>Bộ nhớ</th>
                                        @if($product->product_type === 'imei/serial')
                                        <th class="text-start">Số IMEI</th>
                                        @endif
                                        <th class="text-end">Giá của biến thể</th>
                                        <th class="text-end">Tồn kho</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->variants as $v)
                                    <tr>
                                        <td><span class="badge text-bg-secondary">{{ $v->color }}</span></td>
                                        <td><span class="badge text-bg-info">{{ $v->storage }}</span></td>
                                        @if($product->product_type === 'imei/serial')
                                        <td class="text-start">
                                            @if($v->imeis->isNotEmpty())
                                                <div class="small text-monospace">
                                                    @foreach($v->imeis as $imei)
                                                        <div>{{ $imei->imei }}</div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">--</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="text-end">
                                            {{ $v->additional_price > 0 ? '' . number_format($v->additional_price, 0, ',', '.') : '0' }} đ
                                        </td>
                                        <td class="text-end">{{ $v->stock_quantity }}</td>s
                                        <td>
                                            @if($v->status)
                                            <span class="badge text-bg-success">Active</span>
                                            @else
<span class="badge text-bg-secondary">Ẩn</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.variants.show', $v) }}"
                                                class="btn btn-sm btn-light">
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
                    <td colspan="9" class="text-center text-muted py-4">Chưa có sản phẩm nào.</td>
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