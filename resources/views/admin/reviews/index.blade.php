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
    @if(method_exists($reviews, 'hasPages') && $reviews->hasPages())
    <div class="p-3">
        {{ $reviews->withQueryString()->links() }}
    </div>
    @endif
</section>
@endsection
