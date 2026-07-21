<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_week' => ['nullable', 'regex:/^\d{4}-W(?:0[1-9]|[1-4]\d|5[0-3])$/'],
            'end_week' => ['nullable', 'regex:/^\d{4}-W(?:0[1-9]|[1-4]\d|5[0-3])$/'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $startWeek = $request->string('start_week')->toString();
        $endWeek = $request->string('end_week')->toString();
        $selectedProductId = $request->integer('product_id') ?: null;

        // Selecting only one week means reporting that week only.
        $startWeek = $startWeek ?: $endWeek;
        $endWeek = $endWeek ?: $startWeek;

        $startDate = $startWeek ? $this->weekStart($startWeek) : null;
        $endDate = $endWeek ? $this->weekStart($endWeek)->endOfWeek() : null;

        if ($startDate && $endDate && $startDate->gt($endDate)) {
            return back()
                ->withInput()
                ->withErrors(['end_week' => 'Tuần kết thúc phải sau hoặc bằng tuần bắt đầu.']);
        }

        $completedOrders = Order::query()
            ->where('status', 'completed')
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate));

        $selectedProduct = $selectedProductId ? Product::findOrFail($selectedProductId) : null;
        $productRevenueQuery = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->when($startDate, fn ($query) => $query->where('orders.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('orders.created_at', '<=', $endDate))
            ->when($selectedProductId, fn ($query) => $query->where('order_items.product_id', $selectedProductId));

        $selectedProductStats = $selectedProductId
            ? (clone $productRevenueQuery)
                ->selectRaw('COUNT(DISTINCT orders.id) as order_count, COALESCE(SUM(order_items.quantity), 0) as sold_quantity, COALESCE(SUM(order_items.total), 0) as revenue')
                ->first()
            : null;

        $totalRevenue = $selectedProductStats
            ? $selectedProductStats->revenue
            : (clone $completedOrders)->sum('total_amount');
        $totalOrders = $selectedProductStats
            ? $selectedProductStats->order_count
            : (clone $completedOrders)->count();
        $totalCustomers = User::where('role', User::ROLE_CUSTOMER)->count();

        // The aggregation stays database-independent while the selected period is
        // expressed in weeks. It produces a readable daily revenue trend.
        $revenueByDate = $selectedProductId
            ? (clone $productRevenueQuery)
                ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.total) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
            : (clone $completedOrders)
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

        $topRevenueProducts = Product::select(
            'products.*',
            DB::raw('SUM(order_items.total) as revenue'),
            DB::raw('SUM(order_items.quantity) as sold_quantity')
        )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->when($startDate, fn ($query) => $query->where('orders.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('orders.created_at', '<=', $endDate))
            ->when($selectedProductId, fn ($query) => $query->where('products.id', $selectedProductId))
            ->groupBy('products.id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $lowStockVariants = ProductVariant::with('product')
            ->where('stock_quantity', '<', 10)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $orderStatusCounts = Order::select('fulfillment_status', DB::raw('count(*) as count'))
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->groupBy('fulfillment_status')
            ->pluck('count', 'fulfillment_status')
            ->all();

        $warrantyStatusCounts = Warranty::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $recentClaimedWarranties = Warranty::with(['imei', 'order'])
            ->where('status', 'claimed')
            ->latest('created_at')
            ->take(5)
            ->get();

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'totalCustomers',
            'revenueByDate',
            'topRevenueProducts',
            'lowStockVariants',
            'orderStatusCounts',
            'warrantyStatusCounts',
            'recentClaimedWarranties',
            'startWeek',
            'endWeek',
            'startDate',
            'endDate',
            'products',
            'selectedProductId',
            'selectedProduct',
            'selectedProductStats'
        ));
    }

    private function weekStart(string $week): Carbon
    {
        [$year, $weekNumber] = array_map('intval', explode('-W', $week));

        return Carbon::now()->setISODate($year, $weekNumber, Carbon::MONDAY)->startOfDay();
    }
}
