@extends('layouts.admin')

@section('title', 'Kho phụ kiện')
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Kho phụ kiện')
@section('page_title', 'Kho phụ kiện')
@section('page_subtitle', 'Theo dõi tồn kho phụ kiện theo từng biến thể sản phẩm.')

@section('heading_actions')
    <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-in-down"></i> Nhập kho phụ kiện
    </a>
    @if(auth()->user()?->isAdmin())
    <a href="{{ route('admin.inventory.adjustments.create') }}" class="btn btn-warning btn-sm">
        <i class="bi bi-sliders"></i> Điều chỉnh kho
    </a>
    @endif
    <a href="{{ route('admin.stocks') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-phone"></i> Kho IMEI/Serial
    </a>
    <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-clock-history"></i> Lịch sử giao dịch
    </a>
@endsection

@section('content')
<section class="panel mb-4">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-end flex-grow-1">
            <div class="col-lg-5 col-md-6">
                <label class="form-label small text-muted mb-1">Từ khóa</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control form-control-sm"
                    placeholder="Tên sản phẩm, màu hoặc phiên bản">
            </div>

            <div class="col-lg-3 col-md-4">
                <label class="form-label small text-muted mb-1">Thương hiệu</label>
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả thương hiệu --</option>
                    @foreach($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-4 col-md-12 d-flex gap-2 justify-content-lg-end">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.stocks.accessories') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Tổng biến thể phụ kiện</div>
            <div class="fs-4 fw-semibold">{{ $stocks->count() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Tổng tồn</div>
            <div class="fs-4 fw-semibold">{{ $stocks->sum('stock_quantity') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Hết hàng</div>
            <div class="fs-4 fw-semibold">{{ $stocks->where('stock_quantity', 0)->count() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Sắp hết</div>
            <div class="fs-4 fw-semibold">
                {{ $stocks->filter(fn($item) => $item->stock_quantity > 0 && $item->stock_quantity < 5)->count() }}
            </div>
        </div>
    </div>
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Hãng</th>
                    <th>Biến thể</th>
                    <th>Số lượng</th>
                    <th>Trạng thái</th>
                    <th>Ghi chú</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                    <tr>
                        <td>{{ $stock->id }}</td>
                        <td>{{ $stock->product?->name ?? '-' }}</td>
                        <td>{{ $stock->product?->brand?->name ?? '-' }}</td>
                        <td>
                            {{ $stock->color ? 'Loại: ' . $stock->color : 'Không màu' }}
                            {{ $stock->product?->storage ? ' - ' . $stock->product->storage : '' }}
                        </td>
                        <td class="fw-semibold">{{ $stock->stock_quantity }}</td>
                        <td>
                            @if($stock->stock_quantity <= 0)
                                <span class="badge text-bg-danger">Hết hàng</span>
                            @elseif($stock->stock_quantity < 5)
                                <span class="badge text-bg-warning">Sắp hết</span>
                            @else
                                <span class="badge text-bg-success">Còn hàng</span>
                            @endif
                        </td>
                        <td>
                            @if($stock->stock_quantity <= 0)
                                Cần nhập thêm
                            @elseif($stock->stock_quantity < 5)
                                Số lượng thấp, ưu tiên tiếp hàng
                            @else
                                Kho ổn định
                            @endif
                        </td>
                        <td class="text-end">
                            @if(auth()->user()?->isAdmin())
                            <a
                                href="{{ route('admin.inventory.adjustments.create', ['product_variant_id' => $stock->id]) }}"
                                class="btn btn-sm btn-outline-warning">
                                Điều chỉnh
                            </a>
                            @else
                                <span class="text-muted small">Chỉ admin</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Không có dữ liệu phụ kiện
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
