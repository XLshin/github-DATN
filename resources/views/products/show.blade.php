@extends('layouts.app')

@section('content')

<div class="container">

    {{-- Thông tin sản phẩm --}}
    <div class="card mb-4">
        <div class="card-body">

            <h2>{{ $product->name }}</h2>

            <p>
                <strong>Giá:</strong>
                {{ number_format($product->price, 0, ',', '.') }} VNĐ
            </p>

            @if(isset($product->description))
                <p>
                    <strong>Mô tả:</strong>
                    {{ $product->description }}
                </p>
            @endif

        </div>
    </div>

    {{-- Thông báo thành công --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Hiển thị lỗi --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form đánh giá --}}
    @auth

    <div class="card mb-4">
        <div class="card-header">
            <h4>Viết đánh giá</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('reviews.store', $product->id) }}" method="POST">

                @csrf

                <div class="mb-3">
                    <label class="form-label">
                        Đánh giá sao
                    </label>

                    <select name="rating" class="form-control">

                        <option value="5">5 ⭐</option>

                        <option value="4">4 ⭐</option>

                        <option value="3">3 ⭐</option>

                        <option value="2">2 ⭐</option>

                        <option value="1">1 ⭐</option>

                    </select>
                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Nhận xét
                    </label>

                    <textarea
                        name="comment"
                        rows="4"
                        class="form-control"
                    ></textarea>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Gửi đánh giá
                </button>

            </form>

        </div>
    </div>

    @else

    <div class="alert alert-warning">
        Vui lòng đăng nhập để đánh giá sản phẩm.
    </div>

    @endauth

    {{-- Danh sách đánh giá --}}
    <h3 class="mb-3">
        Danh sách đánh giá
    </h3>

    @forelse($product->reviews as $review)

        <div class="card mb-3">

            <div class="card-body">

                <h6>
                    {{ $review->user->name }}
                </h6>

                <p class="mb-1">
                    <strong>
                        {{ $review->rating }}/5 ⭐
                    </strong>
                </p>

                <p>
                    {{ $review->comment }}
                </p>

                <small class="text-muted">
                    {{ $review->created_at->format('d/m/Y H:i') }}
                </small>

            </div>

        </div>

    @empty

        <div class="alert alert-info">
            Chưa có đánh giá nào.
        </div>

    @endforelse

</div>

@endsection
