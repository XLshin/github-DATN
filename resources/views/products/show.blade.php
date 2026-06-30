@extends('layouts.client')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <h2 class="text-primary">iPhone 15 Pro Max</h2>
            <p class="text-danger fw-bold fs-4">29.990.000 đ</p>
            <p>Mô tả: Điện thoại iPhone thế hệ mới vỏ Titan siêu bền.</p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm p-4">
            <h4>Gửi đánh giá của bạn</h4>

            <form action="{{ route('reviews.store', ['product' => 1]) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Chọn số sao (Rating):</label>
                    <select name="rating" class="form-select">
                        <option value="5">⭐⭐⭐⭐⭐ 5 Sao</option>
                        <option value="4">⭐⭐⭐⭐ 4 Sao</option>
                        <option value="3">⭐⭐⭐ 3 Sao</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nội dung đánh giá:</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Nhập cảm nhận của bạn..."></textarea>
                </div>

                <button type="submit" class="btn btn-warning w-100 fw-bold">Gửi đánh giá ngay</button>
            </form>
        </div>
    </div>
</div>
@endsection
