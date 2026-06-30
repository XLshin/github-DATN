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
                    <h5 class="mb-1">Top 5 sản phẩm bán chạy</h5>
                    <div class="text-muted small">
                        Danh sách sản phẩm có số lượng bán cao nhất.
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-end">Số lượng bán</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bestSellingProducts as $product)
                        <tr>
                            <td class="fw-semibold">
                                {{ $product->name }}
                            </td>

                            <td class="text-end">
                                <span class="badge text-bg-success">
                                    {{ number_format($product->sold_quantity, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">
                                Chưa có dữ liệu bán hàng.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Sản phẩm sắp hết hàng</h5>
                    <div class="text-muted small">
                        Các sản phẩm cần kiểm tra và bổ sung tồn kho.
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-end">Tồn kho</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($lowStockProducts as $product)
                        <tr>
                            <td class="fw-semibold">
                                {{ $product->name }}
                            </td>

                            <td class="text-end">
                                {{ number_format($product->stock_quantity, 0, ',', '.') }}
                            </td>

                            <td>
                                @if($product->stock_quantity == 0)
                                    <span class="badge text-bg-danger">Hết hàng</span>
                                    @else
                                    <span class="badge text-bg-warning">Sắp hết</span>
                                    @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                Không có sản phẩm nào sắp hết hàng.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

@endsection