@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_icon', 'bi-speedometer2')
@section('page_eyebrow', 'Tổng quan hệ thống')
@section('page_title', 'Dashboard ByteZone')
@section('page_subtitle', 'Theo dõi doanh thu, đơn hàng, khách hàng, sản phẩm bán chạy và tồn kho thấp.')

@section('heading_actions')
<a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm"><i class="bi bi-receipt"></i> Đơn hàng</a>
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm"><i class="bi bi-box-seam"></i> Sản phẩm</a>
<a href="{{ route('admin.stocks') }}" class="btn btn-primary btn-sm"><i class="bi bi-boxes"></i> Tồn kho</a>
@endsection

@section('content')
<section class="panel p-3 mb-4">
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="start_week" class="form-label mb-1">Từ tuần</label>
            <input type="week" id="start_week" name="start_week" value="{{ $startWeek }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="end_week" class="form-label mb-1">Đến tuần</label>
            <input type="week" id="end_week" name="end_week" value="{{ $endWeek }}" class="form-control">
            @error('end_week')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="product_id" class="form-label mb-1">Sản phẩm</label>
            <select id="product_id" name="product_id" class="form-select">
                <option value="">Tất cả sản phẩm</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($selectedProductId === $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
            @error('product_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Lọc thống kê</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">Xóa lọc</a>
        </div>
    </form>
    <div class="text-muted small mt-3">
        @if ($startDate)
            Đang xem dữ liệu từ {{ $startDate->format('d/m/Y') }} đến {{ $endDate->format('d/m/Y') }}. Doanh thu chỉ tính đơn hoàn thành.
        @else
            Đang xem toàn bộ thời gian. Chọn một tuần hoặc khoảng tuần để lọc doanh thu và sản phẩm.
        @endif
    </div>
</section>

@if ($selectedProduct)
    <section class="panel p-3 mb-4 border-start border-4 border-primary">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="text-muted small">Sản phẩm đang lọc</div>
                <div class="fw-semibold">{{ $selectedProduct->name }}</div>
            </div>
            <div><span class="text-muted small">Số đơn đã bán</span><div class="fs-5 fw-semibold">{{ number_format($selectedProductStats->order_count) }} đơn</div></div>
            <div><span class="text-muted small">Số lượng bán</span><div class="fs-5 fw-semibold">{{ number_format($selectedProductStats->sold_quantity) }}</div></div>
            <div><span class="text-muted small">Doanh thu sản phẩm</span><div class="fs-5 fw-semibold text-success">{{ number_format($selectedProductStats->revenue, 0, ',', '.') }} đ</div></div>
        </div>
    </section>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3"><section class="panel p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Tổng doanh thu</div><div class="fs-4 fw-semibold mt-1">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div></div><div class="fs-3 text-muted"><i class="bi bi-cash-stack"></i></div></div></section></div>
    <div class="col-md-3"><section class="panel p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Đơn hoàn thành</div><div class="fs-4 fw-semibold mt-1">{{ number_format($totalOrders, 0, ',', '.') }}</div></div><div class="fs-3 text-muted"><i class="bi bi-cart-check"></i></div></div></section></div>
    <div class="col-md-3"><section class="panel p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Tổng khách hàng</div><div class="fs-4 fw-semibold mt-1">{{ number_format($totalCustomers, 0, ',', '.') }}</div></div><div class="fs-3 text-muted"><i class="bi bi-people"></i></div></div></section></div>
    <div class="col-md-3"><section class="panel p-3 h-100"><div class="text-muted small">Đơn hàng theo trạng thái</div><div class="d-flex flex-wrap gap-2 mt-3">@php($statusCards = ['pending' => ['Chờ xác nhận', 'warning'], 'shipping' => ['Đang giao', 'primary'], 'completed' => ['Hoàn thành', 'success'], 'cancelled' => ['Đã hủy', 'danger']]) @foreach($statusCards as $key => [$label, $color])<a href="{{ route('admin.orders.index', ['tab' => $key]) }}" class="badge text-bg-{{ $color }} text-decoration-none">{{ $label }}: {{ number_format($orderStatusCounts[$key] ?? 0) }}</a>@endforeach</div></section></div>
</div>

<div class="row g-3 mb-4">
    {{-- HÀNG 1 - TRÁI: Doanh thu theo thời gian --}}
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Doanh thu theo thời gian</h5>
                    <div class="text-muted small">Biến động doanh thu đơn hoàn thành trong khoảng tuần đã chọn.</div>
                </div>
            </div>
            <div class="p-4">
                <canvas id="revenueTimelineChart" height="280"></canvas>
            </div>
        </section>
    </div>

    {{-- HÀNG 1 - PHẢI: Đơn hàng cần xử lý + Bảo hành (Đặt chung 1 cột để lấp khoảng trống) --}}
    <div class="col-lg-6 d-flex flex-column gap-3">
        <!-- Block Đơn hàng cần xử lý -->
        <section class="panel flex-fill">
            <div class="panel-header">
                <div><h5 class="mb-1">Đơn hàng cần xử lý</h5><div class="text-muted small">Truy cập nhanh các đơn đang chờ thao tác.</div></div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm">Tất cả đơn</a>
            </div>
            @php($actionStatuses = ['pending' => ['Chờ xác nhận', 'bi-hourglass-split', 'warning'], 'waiting_pack' => ['Chờ đóng gói', 'bi-box-seam', 'primary'], 'waiting_handover' => ['Chờ bàn giao', 'bi-truck', 'info'], 'failed' => ['Giao thất bại', 'bi-exclamation-octagon', 'danger']])
            <div class="list-group list-group-flush">
                @foreach ($actionStatuses as $key => [$label, $icon, $color])
                    <a href="{{ route('admin.orders.index', ['tab' => $key]) }}" class="list-group-item list-group-item-action px-4 py-2 d-flex justify-content-between align-items-center">
                        <span><i class="bi {{ $icon }} text-{{ $color }} me-2"></i>{{ $label }}</span>
                        <span class="badge text-bg-{{ $color }} rounded-pill">{{ number_format($orderStatusCounts[$key] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Block Trạng thái Bảo hành (Lấp đúng vào khoảng trống trắng bên dưới) -->
        <section class="panel flex-fill">
            <div class="panel-header">
                <div><h5 class="mb-1">Trạng thái Bảo hành</h5><div class="text-muted small">Thống kê nhanh các phiếu bảo hành.</div></div>
                <a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">Tất cả phiếu</a>
            </div>
            @php($warrantyStatuses = ['claimed' => ['Đang xử lý', 'bi-tools', 'warning'], 'active' => ['Hoàn tất xử lý', 'bi-check-circle', 'success'], 'expired' => ['Hết hạn', 'bi-calendar-x', 'secondary']])
            <div class="list-group list-group-flush">
                @foreach ($warrantyStatuses as $key => [$label, $icon, $color])
                    <a href="{{ route('admin.warranties.index', ['status' => $key]) }}" class="list-group-item list-group-item-action px-4 py-2 d-flex justify-content-between align-items-center">
                        <span><i class="bi {{ $icon }} text-{{ $color }} me-2"></i>{{ $label }}</span>
                        <span class="badge text-bg-{{ $color }} rounded-pill">{{ number_format($warrantyStatusCounts[$key] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- HÀNG 2 - TRÁI: Doanh thu Top 5 sản phẩm --}}
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Doanh thu Top 5 sản phẩm</h5>
                    <div class="text-muted small">Chỉ các sản phẩm phát sinh trong khoảng thời gian đã lọc.</div>
                </div>
            </div>
            <div class="p-4">
                <canvas id="topRevenueChart" height="280"></canvas>
            </div>
        </section>
    </div>

    {{-- HÀNG 2 - PHẢI: Tồn kho biến thể thấp --}}
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div><h5 class="mb-1">Tồn kho biến thể thấp</h5><div class="text-muted small">Các biến thể có tồn kho dưới 10, ưu tiên kiểm tra các mặt hàng đã hết.</div></div>
                <a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">Xem kho</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($lowStockVariants as $variant)
                    <div class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center gap-3">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $variant->product?->name ?? 'Sản phẩm không xác định' }}</div>
                            <div class="small text-muted">Màu: {{ $variant->color ?: '-' }} · Dung lượng: {{ $variant->product?->storage ?: '-' }}</div>
                        </div>
                        <div class="text-end text-nowrap">
                            <span class="badge {{ $variant->stock_quantity <= 0 ? 'text-bg-danger' : 'text-bg-warning' }}">{{ $variant->stock_quantity <= 0 ? 'Hết hàng' : 'Sắp hết' }}</span>
                            <div class="fw-semibold mt-1">Tồn kho:  {{ number_format($variant->stock_quantity) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted"><i class="bi bi-check-circle text-success me-1"></i> Tồn kho hiện đang ở mức an toàn.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="row g-3">
    {{-- HÀNG 3: Phiếu bảo hành chờ xử lý (Kéo dài 100% chiều rộng để dễ quan sát) --}}
    <div class="col-12">
        <section class="panel h-100">
            <div class="panel-header">
                <div><h5 class="mb-1">Phiếu bảo hành chờ xử lý</h5><div class="text-muted small">Các phiếu đang ở trạng thái Đang xử lý bảo hành cần duyệt gấp.</div></div>
                <a href="{{ route('admin.warranties.index', ['status' => 'claimed']) }}" class="btn btn-light btn-sm">Xem tất cả</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($recentClaimedWarranties as $warranty)
                    <a href="{{ route('admin.warranties.show', $warranty) }}" class="list-group-item list-group-item-action px-4 py-3 d-flex justify-content-between align-items-center gap-3">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $warranty->warranty_code }}</div>
                            <div class="small text-muted">IMEI: {{ $warranty->imei->imei ?? 'N/A' }} · Khách hàng: {{ $warranty->order->customer_name ?? 'N/A' }}</div>
                        </div>
                        <div class="text-end text-nowrap">
                            <span class="badge text-bg-warning">Đang xử lý</span>
                            <div class="fw-semibold mt-1">{{ $warranty->created_at?->format('d/m/Y') }}</div>
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted"><i class="bi bi-check-circle text-success me-1"></i> Không có phiếu bảo hành nào đang chờ xử lý.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const revenueLabels = @json($revenueByDate->map(fn ($item) => \Carbon\Carbon::parse($item->date)->format('d/m')));
    const revenueData = @json($revenueByDate->pluck('revenue')->map(fn ($value) => round($value, 0)));
    const topRevenueLabels = @json($topRevenueProducts->pluck('name'));
    const topRevenueData = @json($topRevenueProducts->pluck('revenue')->map(fn ($value) => round($value, 0)));
    const money = value => Number(value).toLocaleString('vi-VN') + ' đ';
    function drawChart(id, type, labels, data, label, color) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        new Chart(canvas, { type, data: { labels, datasets: [{ label, data, backgroundColor: color, borderColor: color, borderWidth: 2, fill: type === 'line', tension: .25 }] }, options: { responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: context => label + ': ' + money(context.parsed.y) } } }, scales: { y: { beginAtZero: true, ticks: { callback: value => Number(value).toLocaleString('vi-VN') } } } } });
    }

    drawChart('revenueTimelineChart', 'line', revenueLabels, revenueData, 'Doanh thu', 'rgba(59, 130, 246, .8)');
    drawChart('topRevenueChart', 'bar', topRevenueLabels, topRevenueData, 'Doanh thu', 'rgba(34, 197, 94, .8)');
});
</script>
@endpush
@endsection
