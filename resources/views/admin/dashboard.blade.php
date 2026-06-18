@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">
        Dashboard ByteZone
    </h2>

    <div class="row">

        <!-- Tổng doanh thu -->
        <div class="col-md-4">
            <div class="card mb-3">

                <div class="card-body">

                    <h5>Tổng doanh thu</h5>

                    <h3>
                        {{ number_format($totalRevenue) }} VNĐ
                    </h3>

                </div>

            </div>
        </div>

        <!-- Tổng đơn hàng -->
        <div class="col-md-4">
            <div class="card mb-3">

                <div class="card-body">

                    <h5>Tổng đơn hàng</h5>

                    <h3>
                        {{ $totalOrders }}
                    </h3>

                </div>

            </div>
        </div>

        <!-- Tổng khách hàng -->
        <div class="col-md-4">
            <div class="card mb-3">

                <div class="card-body">

                    <h5>Tổng khách hàng</h5>

                    <h3>
                        {{ $totalCustomers }}
                    </h3>

                </div>

            </div>
        </div>

    </div>

    <!-- Top sản phẩm bán chạy -->

    <div class="card mt-4">

        <div class="card-header">
            Top 5 sản phẩm bán chạy
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng bán</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($bestSellingProducts as $product)

                    <tr>

                        <td>
                            {{ $product->name }}
                        </td>

                        <td>
                            {{ $product->sold_quantity }}
                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="2">
                            Chưa có dữ liệu
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <!-- Tồn kho thấp -->

    <div class="card mt-4">

        <div class="card-header">
            Sản phẩm sắp hết hàng
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Tồn kho</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($lowStockProducts as $product)

                    <tr>

                        <td>
                            {{ $product->name }}
                        </td>

                        <td>
                            {{ $product->stock_quantity }}
                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="2">
                            Không có sản phẩm nào sắp hết hàng
                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
