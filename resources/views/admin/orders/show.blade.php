@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Chi tiết đơn hàng</h3>
            <div class="text-muted">
                Mã đơn:
                <strong>{{ $order->order_code }}</strong>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                Quay lại
            </a>

            <a href="{{ route('admin.orders.printShippingLabel', $order) }}" target="_blank" class="btn btn-outline-primary">
                In phiếu
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $fulfillmentLabels = [
            'pending' => 'Chờ xử lý',
            'waiting_pack' => 'Chờ đóng gói',
            'waiting_handover' => 'Chờ bàn giao',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'failed' => 'Giao thất bại',
        ];

        $fulfillmentClasses = [
            'pending' => 'bg-warning text-dark',
            'waiting_pack' => 'bg-info text-dark',
            'waiting_handover' => 'bg-primary',
            'shipping' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'failed' => 'bg-danger',
        ];

        $paymentStatus = $order->payment->payment_status ?? null;

        $paymentLabels = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
        ];

        $paymentClasses = [
            'pending' => 'bg-warning text-dark',
            'paid' => 'bg-success',
            'failed' => 'bg-danger',
            'cancelled' => 'bg-danger',
            'refunded' => 'bg-secondary',
        ];
    @endphp

    {{-- Thông tin đơn hàng --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Thông tin đơn hàng</h5>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Mã đơn</div>
<div class="fw-semibold">{{ $order->order_code }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Tiến trình xử lý</div>
                    <div>
                        <span class="badge {{ $fulfillmentClasses[$order->fulfillment_status] ?? 'bg-secondary' }}">
                            {{ $fulfillmentLabels[$order->fulfillment_status] ?? $order->fulfillment_status }}
                        </span>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Thanh toán</div>
                    <div>
                        <span class="badge {{ $paymentClasses[$paymentStatus] ?? 'bg-secondary' }}">
                            {{ $paymentLabels[$paymentStatus] ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Tổng tiền</div>
                    <div class="fw-bold">
                        {{ number_format($order->total_amount, 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thông tin khách hàng + thanh toán --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin khách hàng</h5>

                    <div class="mb-2">
                        <div class="text-muted small">Tên khách hàng</div>
                        <div class="fw-semibold">{{ $order->customer_name }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Số điện thoại</div>
                        <div>{{ $order->customer_phone }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Địa chỉ giao hàng</div>
                        <div>{{ $order->shipping_address }}</div>
                    </div>

                    <div>
                        <div class="text-muted small">Tài khoản</div>
                        <div>{{ $order->user->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Thanh toán</h5>

                    <div class="mb-2">
                        <div class="text-muted small">Phương thức</div>
                        <div class="fw-semibold">
                            {{ strtoupper($order->payment->payment_method ?? '-') }}
                        </div>
                    </div>

                    <div class="mb-2">
<div class="text-muted small">Trạng thái thanh toán</div>
                        <span class="badge {{ $paymentClasses[$paymentStatus] ?? 'bg-secondary' }}">
                            {{ $paymentLabels[$paymentStatus] ?? '-' }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Số tiền</div>
                        <div class="fw-bold">
                            {{ number_format($order->payment->amount ?? 0, 0, ',', '.') }} đ
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small">Mã giao dịch</div>
                        <div>{{ $order->payment->transaction_code ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thao tác xử lý --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Thao tác xử lý</h5>

            <div class="d-flex flex-wrap gap-2">

                @if($order->fulfillment_status === 'pending')
                    <form action="{{ route('admin.orders.confirm', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Xác nhận đơn
                        </button>
                    </form>
                @endif

                @if($order->fulfillment_status === 'waiting_pack')
                    <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#packedModal">
                        Xác nhận đóng gói
                    </button>
                @endif

                @if($order->fulfillment_status === 'waiting_handover')
                    <form action="{{ route('admin.orders.handover', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Bàn giao / Bắt đầu giao
                        </button>
                    </form>
                @endif

                @if($order->fulfillment_status === 'shipping')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#deliveredModal">
                        Giao thành công
                    </button>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#failedModal">
                        Giao thất bại
                    </button>
                @endif

                @if($order->fulfillment_status === 'failed')
                    <form action="{{ route('admin.orders.retryDelivery', $order) }}" method="POST"
                          onsubmit="return confirm('Bạn có chắc muốn giao lại đơn hàng này không?');">
                        @csrf
<button type="submit" class="btn btn-primary">
                            Giao lại
                        </button>
                    </form>
                @endif

                @if(!in_array($order->fulfillment_status, ['completed', 'cancelled'], true))
                    <form action="{{ route('admin.orders.cancel', $order) }}" method="POST"
                          onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            Hủy đơn
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>

    {{-- Tiến trình xử lý --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-1">Tiến trình xử lý</h5>
            <p class="text-muted mb-4">Các mốc thời gian xử lý đơn hàng.</p>

            <div class="row g-3">
                <div class="col-md-2 col-6">
                    <div class="text-muted small">Xác nhận</div>
                    <div class="fw-semibold">
                        {{ $order->confirmed_at ? $order->confirmed_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="text-muted small">Đóng gói</div>
                    <div class="fw-semibold">
                        {{ $order->packed_at ? $order->packed_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="text-muted small">Bàn giao</div>
                    <div class="fw-semibold">
                        {{ $order->handed_over_at ? $order->handed_over_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="text-muted small">Giao thành công</div>
                    <div class="fw-semibold">
                        {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="text-muted small">Đã hủy</div>
                    <div class="fw-semibold">
                        {{ $order->cancelled_at ? $order->cancelled_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <div class="text-muted small">In phiếu</div>
                    <div class="fw-semibold">
                        {{ $order->shipping_label_printed_at ? $order->shipping_label_printed_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- Sản phẩm trong đơn --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-1">Sản phẩm trong đơn</h5>
            <p class="text-muted mb-4">Danh sách sản phẩm, số lượng, biến thể và IMEI nếu có.</p>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Biến thể</th>
                            <th>IMEI</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $item->product->name ?? 'Sản phẩm đã xóa' }}
                                </td>

                                <td>
                                    @if($item->variant)
                                        <div class="fw-semibold">
                                            {{ $item->variant->color ?? 'Không có màu' }}
                                        </div>

                                        @if(!empty($item->variant->storage))
                                            <small class="text-muted">
                                                {{ $item->variant->storage }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($item->imei)
                                        <span class="fw-semibold">
                                            {{ $item->imei->imei ?? $item->imei->serial_number ?? '-' }}
                                        </span>

                                        @if(!empty($item->imei->status))
                                            <div>
                                                <small class="text-muted">
                                                    Trạng thái: {{ $item->imei->status }}
                                                </small>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{ $item->quantity }}
</td>

                                <td class="text-end fw-semibold">
                                    {{ number_format($item->total, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="4" class="text-end fw-bold">Tổng tiền</td>
                            <td class="text-end fw-bold">
                                {{ number_format($order->total_amount, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ảnh minh chứng --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-1">Ảnh minh chứng</h5>
            <p class="text-muted mb-4">Ảnh đóng gói, giao hàng thành công hoặc giao thất bại.</p>

            @if($order->proofs && $order->proofs->count())
                <div class="row g-3">
                    @foreach($order->proofs as $proof)
                        <div class="col-md-4">
                            <div class="border rounded p-2 h-100">
                                <img src="{{ asset('storage/' . $proof->image_path) }}"
                                     alt="Ảnh minh chứng"
                                     class="img-fluid rounded mb-2"
                                     style="width: 100%; height: 220px; object-fit: cover;">

                                <div class="fw-semibold">
                                    @if($proof->type === 'packed')
                                        Ảnh đóng gói
                                    @elseif($proof->type === 'delivered')
                                        Ảnh giao hàng thành công
                                    @elseif($proof->type === 'failed_delivery')
                                        Ảnh giao hàng thất bại
                                    @else
                                        {{ $proof->type }}
                                    @endif
                                </div>

                                <div class="text-muted small">
                                    Người tạo: {{ $proof->creator->name ?? '-' }}
                                </div>

                                <div class="text-muted small">
                                    Thời gian: {{ $proof->created_at ? $proof->created_at->format('d/m/Y H:i') : '-' }}
                                </div>

                                @if($proof->note)
                                    <div class="mt-2">
                                        {{ $proof->note }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
</div>
            @else
                <div class="text-muted">
                    Chưa có ảnh minh chứng.
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Modal: Xác nhận đóng gói --}}
<div class="modal fade" id="packedModal" tabindex="-1" aria-labelledby="packedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.markPacked', $order) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="packedModalLabel">Xác nhận đóng gói</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ảnh minh chứng đóng gói <span class="text-danger">*</span></label>
                    <input type="file" name="packed_image" class="form-control" accept="image/*" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Nhập ghi chú nếu có"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
                <button type="submit" class="btn btn-primary">
                    Xác nhận
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Giao thành công --}}
<div class="modal fade" id="deliveredModal" tabindex="-1" aria-labelledby="deliveredModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.markDelivered', $order) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="deliveredModalLabel">Xác nhận giao hàng thành công</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ảnh minh chứng giao hàng <span class="text-danger">*</span></label>
                    <input type="file" name="delivered_image" class="form-control" accept="image/*" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Nhập ghi chú nếu có"></textarea>
                </div>
            </div>

            <div class="modal-footer">
<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
                <button type="submit" class="btn btn-success">
                    Xác nhận đã giao
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Giao thất bại --}}
<div class="modal fade" id="failedModal" tabindex="-1" aria-labelledby="failedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.markFailed', $order) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="failedModalLabel">Xác nhận giao hàng thất bại</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ảnh minh chứng giao thất bại</label>
                    <input type="file" name="failed_image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lý do / Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Ví dụ: Khách không nghe máy, sai địa chỉ..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
                <button type="submit" class="btn btn-danger">
                    Xác nhận thất bại
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
