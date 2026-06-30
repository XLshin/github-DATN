@extends('layouts.admin')

@section('title', 'Kho hàng')
@section('page_icon', 'bi-boxes')
@section('page_eyebrow', 'Kho IMEI/Serial')
@section('page_title', 'Kho IMEI/Serial')
@section('page_subtitle', 'Chỉ hiển thị kho điện thoại có mã IMEI/Serial và không bao gồm biến thể phụ kiện.')

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

<section class="panel mb-4">
    <div class="panel-header">

    <form method="GET"
          class="row g-2 flex-grow-1">

        <div class="col-md-6">
<div class="row g-2">

    <div class="col-md-6">
        <input
            type="text"
            name="keyword"
            value="{{ request('keyword') }}"
            class="form-control"
            placeholder="Tên sản phẩm, màu sắc hoặc dung lượng">
    </div>

    <div class="col-md-4">
        <select
            name="brand_id"
            class="form-select">

            <option value="">
                -- Tất cả thương hiệu --
            </option>

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
        <button
            class="btn btn-primary w-100">

            Tìm kiếm

        </button>
    </div>

</div>
        </div>

        <div class="col-md-3 d-flex gap-2">

            <a href="{{ route('admin.stocks') }}"
               class="btn btn-light btn-sm">

                Làm mới

            </a>
        </div>

    </form>

</div>
</section>

<div class="row g-3 mb-4">

<div class="col-md-4">
    <div class="panel p-3">
        <div class="text-muted small">
            Tổng biến thể
        </div>

        <div class="fs-4 fw-semibold">
            {{ $stocks->count() }}
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="panel p-3">
        <div class="text-muted small">
            Hết hàng
        </div>

        <div class="fs-4 fw-semibold">
            {{ $stocks->where('available_count',0)->count() }}
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="panel p-3">
        <div class="text-muted small">
            Sắp hết
        </div>

        <div class="fs-4 fw-semibold">
            {{
                $stocks->filter(
                    fn($item)
                    =>
                    $item->available_count > 0
                    &&
                    $item->available_count < 5
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
                <th>Đã bán</th>
                <th>Bảo hành</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>

        <tbody id="stockTableBody">

        @forelse($stocks as $stock)

            <tr>

                <td>
                    {{ $stock->product?->name }}
                </td>

                <td>
                    {{ $stock->color ?? '-' }}
                </td>

                <td>
                    {{ $stock->storage ?? '-' }}
                </td>

                <td>
                    {{ $stock->product->brand->name ?? '-' }}
                </td>

                <td>
                    @php
                        $hasImei = $stock->imeis()->exists();
                    @endphp
                    @if($hasImei)
                        {{ $stock->available_count }}
                    @else
                        {{ $stock->stock_quantity }}
                    @endif
                </td>

                <td>
                    @php
                        $hasImei = $stock->imeis()->exists();
                    @endphp
                    @if($hasImei)
                        {{ $stock->sold_count }}
                    @else
                        -
                    @endif
                </td>

                <td>
                    @php
                        $hasImei = $stock->imeis()->exists();
                    @endphp
                    @if($hasImei)
                        {{ $stock->warranty_count }}
                    @else
                        -
                    @endif
                </td>

                <td>
                    @php
                        $hasImei = $stock->imeis()->exists();
                        $availableCount = $hasImei ? $stock->available_count : $stock->stock_quantity;
                    @endphp

                    @if($availableCount <= 0)

                        <span class="badge text-bg-danger">
                            Hết hàng
                        </span>

                    @elseif($availableCount < 5)

                        <span class="badge text-bg-warning">
                            Sắp hết
                        </span>

                    @else

                        <span class="badge text-bg-success">
                            Còn hàng
                        </span>

                    @endif

                </td>

                <td>
                    @php
                        $hasImei = $stock->imeis()->exists();
                    @endphp

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

            @php
                $hasImei = $stock->imeis()->exists();
            @endphp

            @if($hasImei)
            <tr class="imei-row">

                <td colspan="9"
                    class="p-0 border-0">

                    <div
                        class="collapse stock-collapse"
                        data-bs-parent="#stockTableBody"
                        id="imei-{{ $stock->id }}">

                        <div class="p-3 border rounded">

                            <table class="table table-sm mb-0">

                                <thead>
                                    <tr>
                                        <th>IMEI/Serial</th>
                                        <th>Ngày Nhập</th>
                                        <th>Trạng thái</th>
                                        <th>Xem chi tiết</th>
                                    </tr>
                                </thead>

                                <tbody>

                                @foreach(
                                    $stock->imeis()->get()
                                    as $imei
                                )

                                    <tr>

                                        <td>
                                            {{ $imei->imei }}
                                        </td>

                                        <td>
                                            {{ $imei->created_at->format('d/m/Y') }}
                                        </td>

                                        <td>

                                            @if($imei->status == 'available')

                                                <span class="badge text-bg-success">
                                                    Còn hàng
                                                </span>

                                            @elseif($imei->status == 'sold')

                                                <span class="badge text-bg-primary">
                                                    Đã bán
                                                </span>
                                            @elseif($imei->status == 'reserved')

                                                <span class="badge text-bg-primary">
                                                    Tạm giữ
                                                </span>

                                            @else

                                                <span class="badge text-bg-warning">
                                                    Bảo hành
                                                </span>

                                            @endif

                                        </td>

                                        <td>

                                            <a href="{{ route('admin.imeis.show',$imei->id) }}"
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
                <td colspan="9"
                    class="text-center text-muted py-4">

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
tbody tr.imei-row:hover{
    background: transparent !important;
}

tbody tr.imei-row > td{
    background: transparent !important;
}

/* Tắt hover cho dòng chứa bảng IMEI */
.imei-row:hover > td{
    --bs-table-bg-state: transparent !important;
    background-color: transparent !important;
}

/* Tắt hover cho bảng IMEI bên trong */
.imei-row .table-sm tbody tr:hover > td{
    --bs-table-bg-state: transparent !important;
}
</style>