<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
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

        // Top 5 sản phẩm theo doanh thu
        $topRevenueProducts = Product::select(
                'products.*',
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('SUM(order_items.quantity) as sold_quantity')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // Biến thể sản phẩm sắp hết hàng
        $lowStockVariants = ProductVariant::with('product')
            ->where('stock_quantity', '<', 10)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $orderStatusCounts = Order::select('fulfillment_status', DB::raw('count(*) as count'))
            ->groupBy('fulfillment_status')
            ->pluck('count', 'fulfillment_status')
            ->all();

        return view(
            'admin.dashboard',
            compact(
                'totalRevenue',
                'totalOrders',
                'totalCustomers',
                'topRevenueProducts',
                'lowStockVariants',
                'orderStatusCounts'
            )
        );
    }
}
