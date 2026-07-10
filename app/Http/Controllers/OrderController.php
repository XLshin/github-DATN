<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('client.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.product', 'payment'])->findOrFail($id);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('client.orders.show', compact('order'));
    }
}
