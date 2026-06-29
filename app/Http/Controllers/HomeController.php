<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Thêm hàm with() và truyền vào mảng chứa TÊN CÁC HÀM LIÊN KẾT (Relationships) 
        // mà bạn đã định nghĩa trong file model Product.php
        $products = Product::with(['category', 'images'])
            ->latest()
            ->paginate(12);

        return view('client.home', compact('products'));
    }
}
