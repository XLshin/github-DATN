@extends('layouts.admin')

@section('title', 'Kho hàng')
@section('page_icon', 'bi-boxes')
@section('page_eyebrow', 'Kho IMEI/Serial')
@section('page_title', 'Kho IMEI/Serial')
@section('page_subtitle', 'Chỉ hiển thị kho sản phẩm quản lý bằng IMEI/Serial.')

@section('heading_actions')
    <a href="{{ route('admin.imeis.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nhập IMEI/Serial
    </a>
    <a href="{{ route('admin.stocks.accessories') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-box-seam"></i> Kho phụ kiện
    </a>
    <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-clock-history"></i> Lịch sử kho
    </a>
@endsection

@section('content')
@php
    $keyword = $keyword ?? trim((string) request('keyword'));
    $matchedImeis = $matchedImeis ?? collect();
    $matchedVariantIds = $matchedImeis
        ->pluck('product_variant_id')
        ->filter()
        ->unique()
        ->values()
        ->all();

    $statusLabels = [
        'available' => ['class' => 'text-bg-success', 'label' => 'Còn hàng'],
        'reserved' => ['class' => 'text-bg-warning', 'label' => 'Đang giữ chỗ'],
        'sold' => ['class' => 'text-bg-danger', 'label' => 'Đã bán'],
        'warranty' => ['class' => 'text-bg-primary', 'label' => 'Bảo hành'],
        'returned' => ['class' => 'text-bg-secondary', 'label' => 'Đã loại khỏi kho'],
    ];
@endphp

<section class="panel mb-4">
    <div class="panel-header">
        <form method="GET" class="row g-2 flex-grow-1">
            <div class="col-lg-7">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            class="form-control"
                            placeholder="IMEI, tên, màu hoặc dung lượng">
                    </div>

                    <div class="col-md-4">
                        <select name="brand_id" class="form-select">
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

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            Tìm kiếm
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 d-flex gap-2">
                <a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>
</section>

@if(($isPartialImeiSearch ?? false) && $keyword !== '')
    <section class="panel mb-4">
        <div class="panel-header align-items-start">
            <div>
                <h5 class="mb-1">IMEI khớp tìm kiếm</h5>
                <div class="text-muted small">
                    Nhập đủ 15 số sẽ mở thẳng chi tiết IMEI nếu có trong hệ thống.
                </div>
            </div>
            <span class="badge text-bg-primary">{{ $matchedImeis->count() }} kết quả</span>
        </div>

        @if($matchedImeis->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>IMEI/Serial</th>
                            <th>Sản phẩm</th>
                            <th>Dung lượng</th>
                            <th>Màu</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matchedImeis as $matchedImei)
                            @php
                                $matchedVariant = $matchedImei->productVariant;
                                $matchedProduct = $matchedVariant?->product;
                                $status = $statusLabels[$matchedImei->status] ?? ['class' => 'text-bg-secondary', 'label' => $matchedImei->status];
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $matchedImei->imei }}</td>
                                <td>{{ $matchedProduct?->name ?? '-' }}</td>
                                <td>{{ $matchedProduct?->storage ?? '-' }}</td>
                                <td>{{ $matchedVariant?->color ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.imeis.show', $matchedImei->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($matchedImeis->count() >= 30)
                <div class="text-muted small mt-2">
                    Đang hiển thị 30 kết quả đầu tiên, hãy nhập thêm số để lọc chính xác hơn.
                </div>
            @endif
        @else
            <div class="text-muted py-3">
                Không tìm thấy IMEI nào chứa "{{ $keyword }}".
            </div>
        @endif
    </section>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Tổng biến thể có imei/serial</div>
            <div class="fs-4 fw-semibold">{{ $stocks->count() }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Còn hàng</div>
            <div class="fs-4 fw-semibold">{{ $stocks->sum('available_count') }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Đang giữ chỗ</div>
            <div class="fs-4 fw-semibold">{{ $stocks->sum('reserved_count') }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel p-3">
            <div class="text-muted small">Sắp hết</div>
            <div class="fs-4 fw-semibold">
                {{
                    $stocks->filter(
                        fn($item) => $item->available_count > 0 && $item->available_count < 5
                    )->count()
                }}
            </div>
        </div>
    </div>
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Màu sắc</th>
                    <th>Dung lượng</th>
                    <th>Hãng</th>
                    <th>Tồn kho</th>
                    <th>Đang giữ</th>
                    <th>Đã bán</th>
                    <th>Bảo hành</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody id="stockTableBody">
                @forelse($stocks as $stock)
                    @php
                        $hasImei = $stock->imeis->isNotEmpty();
                        $availableCount = $hasImei ? $stock->available_count : $stock->stock_quantity;
                        $isMatchedVariant = in_array($stock->id, $matchedVariantIds, true);
                    @endphp

                    <tr>
                        <td>{{ $stock->product?->name }}</td>
                        <td>{{ $stock->color ?? '-' }}</td>
                        <td>{{ $stock->product?->storage ?? '-' }}</td>
                        <td>{{ $stock->product?->brand?->name ?? '-' }}</td>
                        <td>{{ $availableCount }}</td>
                        <td>{{ $hasImei ? $stock->reserved_count : '-' }}</td>
                        <td>{{ $hasImei ? $stock->sold_count : '-' }}</td>
                        <td>{{ $hasImei ? $stock->warranty_count : '-' }}</td>
                        <td>
                            @if($availableCount <= 0)
                                <span class="badge text-bg-danger">Hết hàng</span>
                            @elseif($availableCount < 5)
                                <span class="badge text-bg-warning">Sắp hết</span>
                            @else
                                <span class="badge text-bg-success">Còn hàng</span>
                            @endif
                        </td>
                        <td>
                            @if($hasImei)
                                <button
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#imei-{{ $stock->id }}">
                                    Xem IMEI
                                </button>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                    </tr>

                    @if($hasImei)
                        <tr class="imei-row">
                            <td colspan="10" class="p-0 border-0">
                                <div
                                    class="collapse stock-collapse {{ $isMatchedVariant ? 'show' : '' }}"
                                    data-bs-parent="#stockTableBody"
                                    id="imei-{{ $stock->id }}">
                                    <div class="p-3 border rounded">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>IMEI/Serial</th>
                                                    <th>Ngày nhập</th>
                                                    <th>Trạng thái</th>
                                                    <th>Xem chi tiết</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($stock->imeis as $imei)
                                                    @php
                                                        $isMatchedImei = ($isPartialImeiSearch ?? false)
                                                            && $keyword !== ''
                                                            && str_contains($imei->imei, $keyword);
                                                        $imeiStatus = $statusLabels[$imei->status] ?? ['class' => 'text-bg-secondary', 'label' => $imei->status];
                                                    @endphp

                                                    <tr class="{{ $isMatchedImei ? 'table-warning' : '' }}">
                                                        <td class="fw-semibold">{{ $imei->imei }}</td>
                                                        <td>{{ $imei->created_at?->format('d/m/Y') ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge {{ $imeiStatus['class'] }}">
                                                                {{ $imeiStatus['label'] }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('admin.imeis.show', $imei->id) }}"
                                                               class="btn btn-sm btn-outline-primary">
                                                                Chi tiết
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Không có dữ liệu
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

<style>
tbody tr.imei-row:hover {
    background: transparent !important;
}

tbody tr.imei-row > td {
    background: transparent !important;
}

.imei-row:hover > td {
    --bs-table-bg-state: transparent !important;
    background-color: transparent !important;
}

.imei-row .table-sm tbody tr:hover > td {
    --bs-table-bg-state: transparent !important;
}
</style>
