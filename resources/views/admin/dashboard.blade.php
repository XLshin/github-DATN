@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_icon', 'bi-speedometer2')
@section('page_eyebrow', 'Tổng quan hệ thống')
@section('page_title', 'Dashboard ByteZone')
@section('page_subtitle', 'Theo dõi doanh thu, đơn hàng, khách hàng, sản phẩm bán chạy và tồn kho thấp.')

@section('heading_actions')
<a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-receipt"></i> Đơn hàng
</a>

<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-box-seam"></i> Sản phẩm
</a>

<a href="{{ route('admin.stocks') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-boxes"></i> Tồn kho
</a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <section class="panel p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Tổng doanh thu</div>
                    <div class="fs-4 fw-semibold mt-1">
                        {{ number_format($totalRevenue, 0, ',', '.') }} đ
                    </div>
                </div>

                <div class="fs-3 text-muted">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-4">
        <section class="panel p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Tổng đơn hàng</div>
                    <div class="fs-4 fw-semibold mt-1">
                        {{ number_format($totalOrders, 0, ',', '.') }}
                    </div>
                </div>

                <div class="fs-3 text-muted">
                    <i class="bi bi-cart-check"></i>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-4">
        <section class="panel p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Tổng khách hàng</div>
                    <div class="fs-4 fw-semibold mt-1">
                        {{ number_format($totalCustomers, 0, ',', '.') }}
                    </div>
                </div>

                <div class="fs-3 text-muted">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Doanh thu Top 5 sản phẩm</h5>
                    <div class="text-muted small">
                        Biểu đồ doanh thu của 5 sản phẩm có doanh thu cao nhất.
                    </div>
                </div>
            </div>

            <div class="p-4">
                <canvas id="topRevenueChart" height="280"></canvas>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Tồn kho biến thể thấp</h5>
                    <div class="text-muted small">
                        Biểu đồ các biến thể sản phẩm có tồn kho dưới 10.
                    </div>
                </div>
            </div>

            <div class="p-4">
                <canvas id="lowStockChart" height="280"></canvas>
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topRevenueLabels = @json($topRevenueProducts->pluck('name'));
        const topRevenueData = @json($topRevenueProducts->pluck('revenue')->map(fn($value) => round($value, 0)));

        const lowStockLabels = @json($lowStockVariants->map(fn($variant) => ($variant->product?->name ?? 'Sản phẩm') . ' - ' . $variant->color . ($variant->product?->storage ? ' / ' . $variant->product->storage : '')));
        const lowStockData = @json($lowStockVariants->pluck('stock_quantity'));

        const topRevenueCtx = document.getElementById('topRevenueChart');
        if (topRevenueCtx) {
            new Chart(topRevenueCtx, {
                type: 'bar',
                data: {
                    labels: topRevenueLabels,
                    datasets: [{
                        label: 'Doanh thu (đ)',
                        data: topRevenueData,
                        backgroundColor: 'rgba(34, 197, 94, 0.75)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': ' + Number(context.parsed.y).toLocaleString('vi-VN') + ' đ';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: value => value.toLocaleString('vi-VN') }
                        }
                    }
                }
            });
        }

        const lowStockCtx = document.getElementById('lowStockChart');
        if (lowStockCtx) {
            new Chart(lowStockCtx, {
                type: 'bar',
                data: {
                    labels: lowStockLabels,
                    datasets: [{
                        label: 'Tồn kho',
                        data: lowStockData,
                        backgroundColor: 'rgba(248, 113, 113, 0.75)',
                        borderColor: 'rgba(248, 113, 113, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection
