<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|max:1000',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Đánh giá thành công!');
    }

    public function hide(Review $review)
{
    $review->update([
        'status' => false
    ]);

    return back()->with(
        'success',
        'Đã ẩn đánh giá'
    );
}

public function destroy(Review $review)
{
    $review->delete();

    return back()->with(
        'success',
        'Đã xóa đánh giá'
    );
}

public function index()
{
    $reviews = Review::with([
        'user',
        'product'
    ])->latest()->get();

    return view(
        'admin.reviews.index',
        compact('reviews')
    );
}
}
