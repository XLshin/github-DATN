<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('items')->orderBy('created_at', 'desc')->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $order = Order::with('items')->findOrFail($id);

        if ($order->user_id !== $user->id) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }
}
