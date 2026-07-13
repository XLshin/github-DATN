@extends('layouts.admin')

@section('title', 'Chi tiết IMEI')

@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Chi tiết IMEI')
@section('page_subtitle', 'Tra cứu vòng đời, đơn hàng và bảo hành của từng IMEI/Serial.')

@php
    $variant = $imei->productVariant;
    $product = $variant?->product;
    $brand = $product?->brand;
    $orderItem = $imei->orderItem;
    $order = $orderItem?->order;
    $warranty = $imei->warranty;

    $statusLabels = [
        'available' => 'Còn hàng',
        'reserved' => 'Đang giữ chỗ',
        'sold' => 'Đã bán',
        'warranty' => 'Đang bảo hành',
        'returned' => 'Đã loại khỏi kho',
    ];

    $statusClasses = [
        'available' => 'text-bg-success',
        'reserved' => 'text-bg-warning',
        'sold' => 'text-bg-danger',
        'warranty' => 'text-bg-primary',
        'returned' => 'text-bg-secondary',
    ];

    $statusNotes = [
        'available' => 'IMEI này đang có thể chọn khi đóng đơn.',
        'reserved' => 'IMEI này đã được giữ cho một dòng đơn hàng, chưa nên cấp cho đơn khác.',
        'sold' => 'IMEI này đã được bán và đang là dữ liệu truy vết cho đơn hàng/bảo hành.',
        'warranty' => 'IMEI này đang nằm trong quy trình bảo hành.',
        'returned' => 'IMEI này đã bị loại khỏi kho do nhập nhầm hoặc điều chỉnh kho.',
    ];

    $fulfillmentLabels = [
        'pending' => 'Chờ xác nhận',
        'waiting_pack' => 'Chờ đóng hàng',
        'waiting_handover' => 'Chờ bàn giao',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn tất',
        'failed' => 'Giao thất bại',
        'cancelled' => 'Đã hủy',
    ];

    $orderStatusLabels = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
        'returned' => 'Đã trả hàng',
    ];

    $warrantyStatusLabels = [
        'active' => 'Hoàn tất xử lý',
        'expired' => 'Hoàn tất xử lý',
        'claimed' => 'Đang xử lý bảo hành',
    ];

    $finalPrice = (float) ($product?->price ?? 0) + (float) ($variant?->additional_price ?? 0);

    $timelineItems = collect([
        [
            'time' => $imei->created_at,
            'event' => 'Nhập kho IMEI',
            'note' => 'IMEI được tạo trong hệ thống.',
        ],
    ]);

    if ($imei->reserved_at) {
        $timelineItems->push([
            'time' => $imei->reserved_at,
            'event' => 'Giữ chỗ cho đơn hàng',
            'note' => $order?->order_code ? 'Đơn ' . $order->order_code : 'Đã gắn với dòng đơn #' . $orderItem?->id,
        ]);
    }

    if ($order && in_array($imei->status, ['sold', 'warranty'], true)) {
        $timelineItems->push([
            'time' => $order->updated_at,
            'event' => 'Ghi nhận bán hàng',
            'note' => 'IMEI thuộc đơn ' . $order->order_code . '.',
        ]);
    }

    if ($warranty) {
        $timelineItems->push([
            'time' => $warranty->created_at,
            'event' => 'Tạo hồ sơ bảo hành',
            'note' => $warrantyStatusLabels[$warranty->status] ?? $warranty->status_label,
        ]);
    }

    if ($imei->status === 'returned') {
        $timelineItems->push([
            'time' => $imei->updated_at,
            'event' => 'Loại khỏi kho',
            'note' => 'IMEI không còn được chọn để đóng đơn.',
        ]);
    }

    $timelineItems = $timelineItems
        ->sortByDesc(fn ($item) => optional($item['time'])->timestamp ?? 0)
        ->values();
@endphp

@section('heading_actions')
<a href="{{ route('admin.imeis.edit', $imei->id) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-sliders"></i>
    Điều chỉnh
</a>
<a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i>
    Quay lại
</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <section class="panel h-100 p-3">
            <div class="text-muted small mb-1">IMEI/Serial</div>
            <div class="fs-5 fw-bold">{{ $imei->imei }}</div>
            <div class="mt-2">
                <span class="badge {{ $statusClasses[$imei->status] ?? 'text-bg-light' }}">
                    {{ $statusLabels[$imei->status] ?? $imei->status }}
                </span>
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-3">
        <section class="panel h-100 p-3">
            <div class="text-muted small mb-1">Phiên bản</div>
            <div class="fw-semibold">{{ $product?->name ?? '-' }}</div>
            <div class="text-muted small mt-1">
                Dung lượng: {{ $product?->storage ?? '-' }}
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-3">
        <section class="panel h-100 p-3">
            <div class="text-muted small mb-1">Màu sắc</div>
            <div class="fw-semibold">{{ $variant?->color ?: 'Không màu' }}</div>
            <div class="text-muted small mt-1">
                Biến thể #{{ $variant?->id ?? '-' }}
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-3">
        <section class="panel h-100 p-3">
            <div class="text-muted small mb-1">Giá bán theo biến thể</div>
            <div class="fs-5 fw-bold">{{ number_format($finalPrice, 0, ',', '.') }} đ</div>
            <div class="text-muted small mt-1">
                Base {{ number_format((float) ($product?->price ?? 0), 0, ',', '.') }} đ
                @if((float) ($variant?->additional_price ?? 0) !== 0.0)
                    + {{ number_format((float) $variant->additional_price, 0, ',', '.') }} đ
                @endif
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <h5 class="mb-0">Thông tin thiết bị</h5>
            </div>

            <div class="p-3">
                <table class="table align-middle mb-0">
                    <tr>
                        <th width="210">Sản phẩm</th>
                        <td>{{ $product?->productGroup?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Phiên bản</th>
                        <td>{{ $product?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dung lượng</th>
                        <td>{{ $product?->storage ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Màu sắc</th>
                        <td>{{ $variant?->color ?: 'Không màu' }}</td>
                    </tr>
                    <tr>
                        <th>Thương hiệu</th>
                        <td>{{ $brand?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Loại quản lý</th>
                        <td>{{ $product?->product_type === 'imei/serial' ? 'IMEI/Serial' : 'Số lượng' }}</td>
                    </tr>
                    <tr>
                        <th>Ngày nhập kho</th>
                        <td>{{ $imei->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Cập nhật gần nhất</th>
                        <td>{{ $imei->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                </table>

                @if($variant)
                    <div class="mt-3">
                        <a href="{{ route('admin.variants.show', $variant->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-seam"></i>
                            Xem chi tiết phiên bản - màu
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <h5 class="mb-0">Trạng thái kho</h5>
            </div>

            <div class="p-3">
                <div class="alert alert-light border mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge {{ $statusClasses[$imei->status] ?? 'text-bg-light' }}">
                            {{ $statusLabels[$imei->status] ?? $imei->status }}
                        </span>
                        <span class="fw-semibold">Trạng thái hiện tại</span>
                    </div>
                    <div class="text-muted small">
                        {{ $statusNotes[$imei->status] ?? 'Chưa có mô tả cho trạng thái này.' }}
                    </div>
                </div>

                <table class="table align-middle mb-0">
                    <tr>
                        <th width="180">Thời điểm giữ chỗ</th>
                        <td>{{ $imei->reserved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <td>
                            @if($orderItem)
                                {{ $order?->order_code ? 'Đơn ' . $order->order_code : '#' . $orderItem->id }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Có thể đóng đơn</th>
                        <td>
                            @if($imei->status === 'available')
                                <span class="badge text-bg-success">Có</span>
                            @else
                                <span class="badge text-bg-secondary">Không</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header d-flex align-items-center justify-content-between gap-2">
                <h5 class="mb-0">Đơn hàng liên quan</h5>
                @if($order)
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-receipt"></i>
                        Xem đơn
                    </a>
                @endif
            </div>

            <div class="p-3">
                @if($order)
                    <table class="table align-middle mb-0">
                        <tr>
                            <th width="190">Mã đơn hàng</th>
                            <td class="fw-semibold">{{ $order->order_code }}</td>
                        </tr>
                        <tr>
                            <th>Khách hàng</th>
                            <td>{{ $order->customer_name ?? $order->user?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Số điện thoại</th>
                            <td>{{ $order->customer_phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Trạng thái đơn</th>
                            <td>{{ $orderStatusLabels[$order->status] ?? $order->status ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tiến trình giao hàng</th>
                            <td>{{ $fulfillmentLabels[$order->fulfillment_status] ?? $order->fulfillment_status ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Giá dòng hàng</th>
                            <td>{{ number_format((float) ($orderItem?->total ?? 0), 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <th>Ngày tạo đơn</th>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                        IMEI này chưa gắn với đơn hàng nào.
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header d-flex align-items-center justify-content-between gap-2">
                <h5 class="mb-0">Bảo hành</h5>
                @if($warranty)
                    <a href="{{ route('admin.warranties.show', $warranty->id) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-shield-check"></i>
                        Xem bảo hành
                    </a>
                @endif
            </div>

            <div class="p-3">
                @if($warranty)
                    <table class="table align-middle mb-0">
                        <tr>
                            <th width="190">Mã bảo hành</th>
                            <td class="fw-semibold">{{ $warranty->warranty_code }}</td>
                        </tr>
                        <tr>
                            <th>Trạng thái</th>
                            <td>
                                <span class="badge text-bg-{{ $warranty->status_badge }}">
                                    {{ $warrantyStatusLabels[$warranty->status] ?? $warranty->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày kích hoạt</th>
                            <td>{{ $warranty->warranty_start?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Hạn bảo hành</th>
                            <td>{{ $warranty->warranty_end?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Hoàn tất xử lý</th>
                            <td>{{ $warranty->completed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-shield fs-2 d-block mb-2"></i>
                        Chưa có hồ sơ bảo hành cho IMEI này.
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="col-12">
        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Mốc nghiệp vụ chính</h5>
            </div>

            <div class="p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="220">Thời gian</th>
                                <th>Sự kiện</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timelineItems as $item)
                                <tr>
                                    <td>{{ $item['time']?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="fw-semibold">{{ $item['event'] }}</td>
                                    <td>{{ $item['note'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
