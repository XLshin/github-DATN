<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected function adminCheck()
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminCheck();

        $orders = Order::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $this->adminCheck();

        $order = Order::with('items', 'user')->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }
}
