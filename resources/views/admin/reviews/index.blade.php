@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Quản lý đánh giá</h2>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Sản phẩm</th>
                <th>Sao</th>
                <th>Nội dung</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>

        <tbody>

        @foreach($reviews as $review)

            <tr>

                <td>{{ $review->id }}</td>

                <td>{{ $review->user->name }}</td>

                <td>{{ $review->product->name }}</td>

                <td>{{ $review->rating }}</td>

                <td>{{ $review->comment }}</td>

                <td>
                    {{ $review->status ? 'Hiển thị' : 'Đã ẩn' }}
                </td>

                <td>
    <div class="d-flex gap-2">
        {{-- Nếu đánh giá đang HIỂN THỊ (status = 1 hoặc true) -> Hiện nút ẨN --}}
        @if($review->status)
            <form action="{{ route('reviews.hide', $review->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-warning btn-sm fw-semibold text-white">Ẩn</button>
            </form>
        {{-- Nếu đánh giá đang ẨN (status = 0 hoặc false) -> Hiện nút HIỂN THỊ --}}
        @else
            <form action="{{ route('reviews.hide', $review->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm fw-semibold">Hiển thị</button>
            </form>
        @endif

        
    </div>
</td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection
