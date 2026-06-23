<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::query()->latest()->paginate(12);

        return view('client.home', compact('products'));
    }
}
