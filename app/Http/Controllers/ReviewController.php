<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:10240',
        ]);

        $canReview = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                    ->where(function ($subQuery) {
                        $subQuery->where('status', 'completed')
                            ->orWhere('fulfillment_status', 'completed');
                    });
            })->exists();

        if (! $canReview) {
            return back()->withErrors([
                'review' => 'Chỉ khách hàng đã mua sản phẩm và đơn hàng đã hoàn thành mới được đánh giá.',
            ]);
        }

        $data = [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => true,
        ];

        // handle attachments upload
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('reviews', 'public');
                $attachments[] = $path;
            }
        }

        if (! empty($attachments)) {
            $data['attachments'] = $attachments;
        }

        Review::create($data);

        return back()->with('success', 'Đánh giá thành công!');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Đã xóa đánh giá');
    }

    public function index()
    {
        $reviews = Review::with(['user', 'product'])->latest()->get();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function hide($id)
    {
        $review = Review::findOrFail($id);
        $review->status = $review->status == 1 ? 0 : 1;
        $review->save();

        return redirect()->back()->with('success', 'Cập nhật trạng thái đánh giá thành công!');
    }
}
