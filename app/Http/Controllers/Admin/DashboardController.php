<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Tổng doanh thu
        $totalRevenue = Order::where(
            'status',
            'completed'
        )->sum('total_amount');

        // Tổng đơn hàng
        $totalOrders = Order::count();

        // Tổng khách hàng
        $totalCustomers = User::count();

        // Top 5 sản phẩm bán chạy
        $bestSellingProducts = Product::select(
                'products.*',
                DB::raw('SUM(order_items.quantity) as sold_quantity')
            )
            ->join(
                'order_items',
                'products.id',
                '=',
                'order_items.product_id'
            )
            ->groupBy('products.id')
            ->orderByDesc('sold_quantity')
            ->take(5)
            ->get();

        // Sản phẩm sắp hết hàng
        $lowStockProducts = Product::where(
            'stock_quantity',
            '<',
            10
        )->get();

        return view(
            'admin.dashboard',
            compact(
                'totalRevenue',
                'totalOrders',
                'totalCustomers',
                'bestSellingProducts',
                'lowStockProducts'
            )
        );
    }
}
