@extends('layouts.admin')

@section('title', 'Đánh giá')
@section('page_icon', 'bi-star')
@section('page_eyebrow', 'Quản lý nội dung')
@section('page_title', 'Quản lý đánh giá')
@section('page_subtitle', 'Theo dõi, ẩn hoặc xóa đánh giá của khách hàng cho từng sản phẩm.')

@section('content')
<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th class="text-center">Sao</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->id }}</td>

                    <td class="fw-semibold">
                        {{ $review->user->name ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $review->product->name ?? 'Không có' }}
                    </td>

                    <td class="text-center">
                        <span class="badge text-bg-warning">
                            {{ $review->rating }} ★
                        </span>
                    </td>

                    <td>
                        {{ $review->comment }}
                    </td>

                    <td>
                        @if($review->status)
                        <span class="badge text-bg-success">
                            Hiển thị
                        </span>
                        @else
                        <span class="badge text-bg-secondary">
                            Đã ẩn
                        </span>
                        @endif
                    </td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if($review->status)
                            <form
                                action="{{ route('reviews.hide', $review->id) }}"
                                method="POST"
                                onsubmit="return confirm('Ẩn đánh giá này?')">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    Ẩn
                                </button>
                            </form>
                            @endif

                            <form
                                action="{{ route('reviews.destroy', $review->id) }}"
                                method="POST"
                                onsubmit="return confirm('Xóa đánh giá này?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa có đánh giá nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($reviews, 'hasPages') && $reviews->hasPages())
    <div class="p-3">
        {{ $reviews->withQueryString()->links() }}
    </div>
    @endif
</section>
@endsection