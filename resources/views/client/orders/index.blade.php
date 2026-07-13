@extends('layouts.app')

@section('title', 'Đơn Hàng Của Tôi - Shopee Style')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-pills nav-justified text-center" style="background-color: #fff;">
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-0 fw-bold text-secondary {{ is_null($status) ? 'active border-bottom border-danger border-3 bg-light text-danger' : '' }}"
                       href="{{ route('orders.index') }}">Tất cả</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-0 fw-bold text-secondary {{ $status === 'pending' ? 'active border-bottom border-danger border-3 bg-light text-danger' : '' }}"
                       href="{{ route('orders.index', ['status' => 'pending']) }}">Chờ xử lý</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-0 fw-bold text-secondary {{ $status === 'shipping' ? 'active border-bottom border-danger border-3 bg-light text-danger' : '' }}"
                       href="{{ route('orders.index', ['status' => 'shipping']) }}">Đang giao</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-0 fw-bold text-secondary {{ $status === 'completed' ? 'active border-bottom border-danger border-3 bg-light text-danger' : '' }}"
                       href="{{ route('orders.index', ['status' => 'completed']) }}">Hoàn thành</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-0 fw-bold text-secondary {{ $status === 'failed' ? 'active border-bottom border-danger border-3 bg-light text-danger' : '' }}"
                    href="{{ route('orders.index', ['status' => 'failed']) }}">
                        Giao thất bại
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 rounded-0 fw-bold text-secondary {{ $status === 'cancelled' ? 'active border-bottom border-danger border-3 bg-light text-danger' : '' }}"
                       href="{{ route('orders.index', ['status' => 'cancelled']) }}">Đã hủy</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">

        <form method="GET" action="{{ route('orders.index') }}">

            {{-- Giữ trạng thái hiện tại --}}
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="row g-3">

                <div class="col-md-5">
                    <input
                        type="text"
                        class="form-control"
                        name="keyword"
                        placeholder="Nhập mã đơn hoặc tên sản phẩm..."
                        value="{{ $keyword }}">
                </div>

                <div class="col-md-2">
                    <input
                        type="date"
                        class="form-control"
                        name="from_date"
                        value="{{ $fromDate }}">
                </div>

                <div class="col-md-2">
                    <input
                        type="date"
                        class="form-control"
                        name="to_date"
                        value="{{ $toDate }}">
                </div>

                <div class="col-md-3 d-flex">

                    <button class="btn btn-danger me-2 flex-fill">

                        <i class="bi bi-search"></i>

                        Tìm kiếm

                    </button>

                    <a href="{{ route('orders.index', ['status' => $status]) }}"
                       class="btn btn-outline-secondary">

                        Đặt lại

                    </a>

                </div>

            </div>

        </form>

    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    @if($orders->isEmpty())
        <div class="card shadow-sm border-0 py-5 text-center">
            <div class="card-body">
                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="No Orders" style="width: 100px;" class="mb-3 opacity-50">
                <p class="text-muted mb-0">Chưa có đơn hàng nào trong mục này.</p>
            </div>
        </div>
    @else
        @foreach($orders as $order)
            <div class="card shadow-sm border mb-4 overflow-hidden order-card">
                <div class="card-header order-header">
                    <div>
                        <span class="fw-bold text-dark text-uppercase">Mã đơn: #{{ $order->order_code }}</span>
                        <span class="mx-2 text-muted">|</span>
                        <small class="text-muted">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div>
                        @php
                            $badgeClass = match($order->fulfillment_status) {
                                'pending' => 'bg-warning text-dark',
                                'waiting_pack', 'waiting_handover', 'shipping' => 'bg-info text-white',
                                'completed' => 'bg-success text-white',
                                'cancelled' => 'bg-danger text-white',
                                default => 'bg-secondary text-white'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} px-3 py-2 fw-semibold">
                            {{ $order->fulfillment_status_label }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @foreach($order->items as $item)
                        <div class="d-flex p-3 border-bottom align-items-center">
                            @php
                                $image =
                                    $item->variant?->image_path
                                    ?? $item->variant?->images->first()?->image_path
                                    ?? $item->product?->images->first()?->image_path;
                            @endphp

                            <img
                                src="{{ $image ? asset('storage/' . $image) : 'https://via.placeholder.com/80' }}"
                                alt="{{ $item->product->name }}"
                                class="rounded border"
                                style="width:80px;height:80px;object-fit:cover;">
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 text-truncate" style="max-width: 600px;">
                                    <a href="{{ route('products.show', $item->product) }}?hide_reviews=1" class="text-dark text-decoration-none">{{ $item->product->name ?? 'Sản phẩm không tồn tại' }}</a>
                                </h6>

                                {{-- Hiển thị biến thể rõ ràng bằng badge --}}
                                @if($item->variant)
                                    <small class="d-block mb-1">
                                        <span class="badge bg-light text-dark border border-secondary-subtle">
                                            {{ $item->variant->color }} - {{ $item->product->storage }}
                                        </span>
                                    </small>
                                @endif

                                <small class="text-dark fw-medium">Số lượng: x{{ $item->quantity }}</small>
                            </div>
                            <div class="text-end">
                                <span class="text-danger fw-bold">
                                    {{ number_format($item->price, 0, ',', '.') }} ₫
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card-footer bg-white order-footer">
                    <div>
                        @if($order->fulfillment_status === 'pending')
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $order->id }}">
                                Hủy đơn hàng
                            </button>
                        @endif
                    </div>
                    <div class="text-end">
                        <p class="mb-2 text-muted small">
                            Thành tiền: <span class="fs-5 text-danger fw-bold">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
                        </p>
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-danger btn-sm px-4">
                            Xem chi tiết đơn hàng
                        </a>
                    </div>
                </div>
            </div>

            @if($order->fulfillment_status === 'pending')
                <div class="modal fade" id="cancelModal-{{ $order->id }}" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="modal-content border-0 shadow">
                            @csrf
                            <div class="modal-header bg-danger text-white border-0 py-3">
                                <h5 class="modal-title fw-bold">Xác Nhận Hủy Đơn Hàng #{{ $order->order_code }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <p class="text-dark fw-medium">Vui lòng điền lý do bạn muốn hủy đơn hàng này. Lưu ý hành động này không thể hoàn tác.</p>
                                <div class="form-group">
                                    <div class="mb-3">

                                        <label class="form-label fw-bold">
                                            Chọn lý do hủy đơn <span class="text-danger">*</span>
                                        </label>

                                        @php
                                            $cancelReasons = [
                                                'Tôi muốn thay đổi địa chỉ nhận hàng',
                                                'Tôi muốn thay đổi sản phẩm',
                                                'Tôi đặt nhầm đơn hàng',
                                                'Tôi tìm được giá tốt hơn',
                                                'Thời gian giao hàng quá lâu',
                                                'Không còn nhu cầu mua',
                                            ];
                                        @endphp

                                        @foreach($cancelReasons as $index => $reason)

                                            <div class="form-check mb-2">

                                                <input
                                                    class="form-check-input reason-radio"
                                                    type="radio"
                                                    name="reason_option"
                                                    id="reason{{ $index }}"
                                                    value="{{ $reason }}"
                                                    required>

                                                <label
                                                    class="form-check-label"
                                                    for="reason{{ $index }}">

                                                    {{ $reason }}

                                                </label>

                                            </div>

                                        @endforeach

                                        <div class="form-check mb-3">

                                            <input
                                                class="form-check-input reason-radio"
                                                type="radio"
                                                name="reason_option"
                                                id="reasonOther"
                                                value="other">

                                            <label
                                                class="form-check-label"
                                                for="reasonOther">

                                                Khác (tự nhập)

                                            </label>

                                        </div>

                                        <textarea
                                            class="form-control d-none custom-reason"
                                            rows="3"
                                            placeholder="Nhập lý do của bạn..."></textarea>

                                        <input
                                            type="hidden"
                                            name="cancel_reason"
                                            class="cancel-reason-hidden">

                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light">
                                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-danger btn-sm px-4">Xác nhận hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach

        <div class="d-flex justify-content-center mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.modal').forEach(function (modal) {

        const radios = modal.querySelectorAll('.reason-radio');

        const textarea = modal.querySelector('.custom-reason');

        const hidden = modal.querySelector('.cancel-reason-hidden');

        radios.forEach(function (radio) {

            radio.addEventListener('change', function () {

                if (this.value === 'other') {

                    textarea.classList.remove('d-none');

                    textarea.required = true;

                    hidden.value = '';

                } else {

                    textarea.classList.add('d-none');

                    textarea.required = false;

                    textarea.value = '';

                    hidden.value = this.value;

                }

            });

        });

        textarea.addEventListener('input', function () {

            hidden.value = this.value;

        });

    });

});

</script>
@endsection
