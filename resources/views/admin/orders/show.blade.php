@extends('layouts.admin')

@section('content')

@php
    $availableImeisByVariant = $availableImeisByVariant ?? collect();

    $missingRequiredImeis = $order->items->contains(function ($item) {
        return ($item->product->product_type ?? null) === 'imei/serial'
            && !$item->hasFullImeiAssignment();
    });

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

<div class="container-fluid py-4">

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

            @if($missingRequiredImeis)
                <button type="button"
                        class="btn btn-outline-secondary"
                        disabled
                        title="Đơn hàng có sản phẩm cần IMEI nhưng chưa được gán IMEI. Hãy đóng gói và nhập IMEI trước.">
                    In phiếu
                </button>
            @else
                <a href="{{ route('admin.orders.printShippingLabel', $order) }}"
                target="_blank"
                class="btn btn-outline-primary">
                    In phiếu
                </a>
            @endif
        </div>
    </div>

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

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin khách hàng</h5>

                    @if($order->buyer_type === 'proxy')
                        <div class="mb-2">
                            <div class="text-muted small">Người đặt mua <span class="badge bg-info text-dark">Mua hộ</span></div>
                            <div class="fw-semibold">{{ $order->buyer_name }} — {{ $order->buyer_phone }}</div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <div class="text-muted small">Tên khách hàng{{ $order->buyer_type === 'proxy' ? ' (người nhận)' : '' }}</div>
                        <div class="fw-semibold">
                            {{ $order->customer_name ?? $order->user->name ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Số điện thoại</div>
                        <div>{{ $order->customer_phone ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Địa chỉ trong đơn</div>
                        <div>{{ $order->shipping_address ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-muted small">Tài khoản</div>
                        <div>{{ $order->user->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-1">Thông tin người nhận</h5>

                    <p class="text-muted small mb-3">
                        Phần này sẽ được in ra phiếu giao hàng.
                    </p>

                    <div class="mb-2">
                        <label class="form-label small text-muted">
                            Tên người nhận
                        </label>

                        <input type="text"
                               class="form-control form-control-sm"
                               value="{{ $order->receiver->receiver_name ?? $order->customer_name ?? '-' }}"
                               readonly>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted">
                            Số điện thoại người nhận
                        </label>

                        <input type="text"
                               class="form-control form-control-sm"
                               value="{{ $order->receiver->receiver_phone ?? $order->customer_phone ?? '-' }}"
                               readonly>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted">
                            Địa chỉ nhận hàng
                        </label>

                        <textarea rows="3"
                                  class="form-control form-control-sm"
                                  readonly>{{ $order->receiver->receiver_address ?? $order->shipping_address ?? '-' }}</textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small text-muted">
                            Ghi chú người nhận
                        </label>

                        <textarea rows="2"
                                  class="form-control form-control-sm"
                                  readonly>{{ $order->receiver->receiver_note ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
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

                    <div class="mb-2">
                        <div class="text-muted small">Mã giao dịch</div>
                        <div>{{ $order->payment->transaction_code ?? '-' }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Người thanh toán</div>
                        <div>
                            {{ $order->payment->payer_name ?? '-' }}
                            @if($order->payment?->payer_note)
                                <div class="text-muted small">{{ $order->payment->payer_note }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Thời điểm thanh toán</div>
                        <div>{{ $order->payment?->paid_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>

                    @if($order->payment?->proof_image)
                        <div class="mb-3">
                            <div class="text-muted small">Ảnh bằng chứng khách gửi</div>
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($order->payment->proof_image) }}" target="_blank">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($order->payment->proof_image) }}"
                                     alt="Bằng chứng thanh toán" class="img-fluid rounded border mt-1" style="max-height:280px">
                            </a>
                        </div>
                    @endif

                    @if($order->payment?->confirmed_by)
                        <div class="mb-2">
                            <div class="text-muted small">Admin xác nhận</div>
                            <div>{{ $order->payment->confirmedBy->name ?? '-' }}</div>
                            @if($order->payment->admin_note)
                                <div class="text-muted small">Ghi chú: {{ $order->payment->admin_note }}</div>
                            @endif
                        </div>
                    @endif

                    @if($order->payment?->rejected_by)
                        <div class="mb-2">
                            <div class="text-muted small">Admin từ chối</div>
                            <div>{{ $order->payment->rejectedBy->name ?? '-' }}</div>
                            <div class="text-danger small">Lý do: {{ $order->payment->reject_reason }}</div>
                        </div>
                    @endif

                    @if($paymentStatus === 'pending')
                        <div class="border rounded p-3 mt-3 bg-light">
                            <div class="fw-semibold small mb-2">Đối soát &amp; xác nhận thanh toán</div>

                            <form action="{{ route('admin.orders.confirmPayment', $order) }}" method="POST" class="mb-3"
                                  onsubmit="return confirm('Xác nhận đã nhận đủ tiền cho đơn hàng này?');">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Số tiền thực nhận <span class="text-danger">*</span></label>
                                    <input type="number" name="confirmed_amount" class="form-control form-control-sm" step="1"
                                           value="{{ (int) ($order->payment->amount ?? 0) }}" required>
                                    <div class="form-text">Phải khớp đúng số tiền đơn hàng ({{ number_format($order->payment->amount ?? 0, 0, ',', '.') }} đ).</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Ghi chú (tùy chọn)</label>
                                    <textarea name="admin_note" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-check2-circle me-1"></i>Xác nhận đã nhận tiền
                                </button>
                            </form>

                            <form action="{{ route('admin.orders.rejectPayment', $order) }}" method="POST"
                                  onsubmit="return confirm('Từ chối yêu cầu thanh toán này?');">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Lý do từ chối</label>
                                    <textarea name="reject_reason" class="form-control form-control-sm" rows="2"
                                              placeholder="VD: Không nhận được tiền, sai số tiền..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-x-circle me-1"></i>Từ chối
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Thao tác xử lý</h5>

            @php
                $isPrepaidOrder = $order->payment && in_array($order->payment->payment_method, ['card', 'bank_transfer', 'momo', 'vnpay'], true);
                $paymentNotConfirmed = $isPrepaidOrder && $order->payment->payment_status !== 'paid';
            @endphp

            <div class="d-flex flex-wrap gap-2">
                @if($order->fulfillment_status === 'pending')
                    @if($paymentNotConfirmed)
                        <button type="button" class="btn btn-primary" disabled
                                title="Đơn trả trước chưa được xác nhận đã nhận tiền — hãy đối soát thanh toán trước.">
                            Xác nhận đơn
                        </button>
                        <div class="text-danger small align-self-center">
                            <i class="bi bi-exclamation-triangle"></i>
                            Chưa thể xác nhận đơn: thanh toán trả trước chưa được duyệt.
                        </div>
                    @else
                        <form action="{{ route('admin.orders.confirm', $order) }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-primary">
                                Xác nhận đơn
                            </button>
                        </form>
                    @endif
                @endif

                @if($order->fulfillment_status === 'waiting_pack')
                    <button type="button"
                            class="btn btn-info text-white"
                            data-bs-toggle="modal"
                            data-bs-target="#packedModal">
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
                    <button type="button"
                            class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#deliveredModal">
                        Giao thành công
                    </button>

                    <button type="button"
                            class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#failedModal">
                        Giao thất bại
                    </button>
                @endif

                @if($order->fulfillment_status === 'failed')
                    <form action="{{ route('admin.orders.retryDelivery', $order) }}"
                          method="POST"
                          onsubmit="return confirm('Bạn có chắc muốn giao lại đơn hàng này không?');">
                        @csrf

                        <button type="submit" class="btn btn-primary">
                            Giao lại
                        </button>
                    </form>
                @endif

                @if(!in_array($order->fulfillment_status, ['completed', 'cancelled'], true))

                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#cancelOrderModal">
                        Hủy đơn
                    </button>

                @endif
            </div>
        </div>
    </div>

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

        @if($order->fulfillment_status === 'cancelled')

        @php
            $cancelProof = $order->proofs
                ->where('type','cancelled')
                ->first();
        @endphp

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <h5 class="text-danger mb-4">
                    Thông tin hủy đơn
                </h5>

                <div class="row">

                    <div class="col-md-4">

                        <div class="text-muted small">
                            Người hủy
                        </div>

                        <div class="fw-semibold">
                            {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách hàng' }}
                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="text-muted small">
                            Thời gian hủy
                        </div>

                        <div class="fw-semibold">
                            {{ optional($order->cancelled_at)->format('d/m/Y H:i') }}
                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="text-muted small">
                            Lý do
                        </div>

                        <div class="fw-semibold">
                            {{ $order->cancel_reason }}
                        </div>

                    </div>

                </div>

                @if($cancelProof)

                    <hr>

                    <h6 class="mb-3">
                        Ảnh minh chứng
                    </h6>

                    <a href="{{ asset('storage/'.$cancelProof->image_path) }}"
                    target="_blank">

                        <img
                            src="{{ asset('storage/'.$cancelProof->image_path) }}"
                            class="img-thumbnail"
                            style="max-width:300px">

                    </a>

                @endif

            </div>

        </div>

        @endif
    </div>

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
                            <th class="text-end">Giá sản phẩm</th>
                            <th class="text-end">Tổng tiền</th>
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

                                        @if(!empty($item->product->storage))
                                            <small class="text-muted">
                                                {{ $item->product->storage }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($item->imeis && $item->imeis->isNotEmpty())
                                        <ul class="list-unstyled mb-0">
                                            @foreach($item->imeis as $assignedImei)
                                                <li class="mb-1">
                                                    <span class="fw-semibold">
                                                        {{ $assignedImei->imei ?? '-' }}
                                                    </span>

                                                    @if(!empty($assignedImei->status))
                                                        <small class="text-muted">
                                                            ({{ $assignedImei->status }})
                                                        </small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>

                                        @if(($item->product->product_type ?? null) === 'imei/serial' && !$item->hasFullImeiAssignment())
                                            <span class="text-danger small">
                                                Còn thiếu {{ $item->remainingImeiSlots() }} IMEI
                                            </span>
                                        @endif
                                    @elseif(($item->product->product_type ?? null) === 'imei/serial')
                                        <span class="text-danger">Chưa gán IMEI</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{ $item->quantity }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($item->price, 0, ',', '.') }} đ
                                </td>

                                <td class="text-end fw-semibold">
                                    {{ number_format($item->total, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="5" class="text-end fw-bold">
                                Tổng đơn hàng
                            </td>

                            <td class="text-end fw-bold">
                                {{ number_format($order->total_amount, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                                    @elseif($proof->type === 'cancelled')
                                         Ảnh hủy đơn
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

@include('admin.orders.partials.packed-modal', [
    'order' => $order,
    'modalId' => 'packedModal',
    'availableImeisByVariant' => $availableImeisByVariant,
])

@include('admin.orders.partials.delivered-modal', [
    'order' => $order,
    'modalId' => 'deliveredModal',
])

<div class="modal fade"
     id="failedModal"
     tabindex="-1"
     aria-labelledby="failedModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.markFailed', $order) }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="failedModalLabel">
                    Xác nhận giao hàng thất bại
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Đóng">
                </button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ảnh minh chứng giao thất bại</label>

                    <input type="file"
                           name="failed_image"
                           class="form-control"
                           accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lý do / Ghi chú</label>

                    <textarea name="note"
                              class="form-control"
                              rows="3"
                              placeholder="Ví dụ: Khách không nghe máy, sai địa chỉ..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Đóng
                </button>

                <button type="submit" class="btn btn-danger">
                    Xác nhận thất bại
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="cancelOrderModal" tabindex="-1">

    <div class="modal-dialog">

        <form action="{{ route('admin.orders.cancel', $order) }}"
                method="POST"
                enctype="multipart/form-data">

            @csrf

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Hủy đơn hàng
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">

                            Lý do hủy <span class="text-danger">*</span>

                        </label>

                        <textarea
                            class="form-control"
                            name="cancel_reason"
                            rows="4"
                            required>{{ old('cancel_reason') }}</textarea>

                            <div class="mt-3">

                                <label class="form-label">

                                    Ảnh minh chứng

                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    name="cancel_image"
                                    accept="image/*">

                            </div>

                        @error('cancel_reason')
                            <div class="text-danger mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Đóng

                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger">

                        Xác nhận hủy đơn

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
@endsection
