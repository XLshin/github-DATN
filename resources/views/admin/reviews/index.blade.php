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

                    @if($review->status)

                    <form
                        action="{{ route('reviews.hide', $review->id) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="btn btn-warning btn-sm">
                            Ẩn
                        </button>
                    </form>

                    @endif

                    <form
                        action="{{ route('reviews.destroy', $review->id) }}"
                        method="POST"
                        class="mt-1"
                    >
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm">
                            Xóa
                        </button>
                    </form>

                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection
