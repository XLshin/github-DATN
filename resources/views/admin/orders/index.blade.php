@extends('layouts.admin')

@section('title', 'Đơn hàng')
@section('page_icon', 'bi-receipt')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Quản lý đơn hàng')
@section('page_subtitle', 'Quản lý xử lý đơn, đóng gói, bàn giao, giao hàng và hoàn thành đơn.')

@section('heading_actions')
<a href="{{ url('/') }}" class="btn btn-light btn-sm">
    <i class="bi bi-house"></i> Về trang chủ
</a>
@endsection

@section('content')

@php
    $availableImeisByVariant = $availableImeisByVariant ?? collect();
@endphp

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
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

<section class="panel mb-3">
    <div class="p-3">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.orders.index') }}"
               class="btn btn-sm {{ request('tab') ? 'btn-light' : 'btn-primary' }}">
                Tất cả
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'unpaid']) }}"
               class="btn btn-sm {{ request('tab') === 'unpaid' ? 'btn-primary' : 'btn-light' }}">
                Chưa thanh toán
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'pending']) }}"
               class="btn btn-sm {{ request('tab') === 'pending' ? 'btn-primary' : 'btn-light' }}">
                Chờ xử lý
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'waiting_pack']) }}"
               class="btn btn-sm {{ request('tab') === 'waiting_pack' ? 'btn-primary' : 'btn-light' }}">
                Chờ đóng gói
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'waiting_handover']) }}"
               class="btn btn-sm {{ request('tab') === 'waiting_handover' ? 'btn-primary' : 'btn-light' }}">
                Chờ bàn giao
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'shipping']) }}"
               class="btn btn-sm {{ request('tab') === 'shipping' ? 'btn-primary' : 'btn-light' }}">
                Đang giao
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'completed']) }}"
               class="btn btn-sm {{ request('tab') === 'completed' ? 'btn-primary' : 'btn-light' }}">
                Hoàn thành
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'failed']) }}"
               class="btn btn-sm {{ request('tab') === 'failed' ? 'btn-primary' : 'btn-light' }}">
                Giao thất bại
            </a>

            <a href="{{ route('admin.orders.index', ['tab' => 'cancelled']) }}"
               class="btn btn-sm {{ request('tab') === 'cancelled' ? 'btn-primary' : 'btn-light' }}">
                Đã hủy
            </a>
        </div>
    </div>
</section>

<section class="panel mb-3">
    <div class="panel-header">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 flex-grow-1">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            <div class="col-md-10">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm theo mã đơn, tên khách hàng, số điện thoại, địa chỉ hoặc người nhận">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Tìm kiếm
                </button>

                <a href="{{ route('admin.orders.index', request('tab') ? ['tab' => request('tab')] : []) }}"
                   class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>
</section>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Người nhận</th>
                    <th>Sản phẩm</th>
                    <th>Trạng thái đơn</th>
                    <th class="text-end">Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Tiến trình xử lý</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-semibold">
                            {{ $order->order_code }}

                            <div class="text-muted small">
                                {{ $order->created_at?->format('d/m/Y H:i') }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $order->customer_name ?? $order->user->name ?? 'Guest' }}
                            </div>

                            <div class="text-muted small">
                                {{ $order->customer_phone ?? '-' }}
                            </div>

                            <div class="text-muted small">
                                {{ $order->shipping_address ?? '-' }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $order->receiver->receiver_name ?? $order->customer_name ?? $order->user->name ?? 'Guest' }}
                            </div>

                            <div class="text-muted small">
                                {{ $order->receiver->receiver_phone ?? $order->customer_phone ?? '-' }}
                            </div>

                            <div class="text-muted small">
                                {{ $order->receiver->receiver_address ?? $order->shipping_address ?? '-' }}
                            </div>

                            @if($order->receiver?->receiver_note)
                                <div class="text-muted small">
                                    Ghi chú: {{ $order->receiver->receiver_note }}
                                </div>
                            @endif
                        </td>

                        <td>
                            @foreach($order->items as $item)
                                <div class="mb-2">
                                    <span class="fw-semibold">
                                        {{ $item->product->name ?? ('Product #' . $item->product_id) }}
                                    </span>

                                    x {{ $item->quantity }}

                                    @if($item->variant)
                                        <div class="text-muted small">
                                            Biến thể:
                                            {{ $item->variant->color ?? '-' }}

                                            @if(!empty($item->variant->storage))
                                                - {{ $item->variant->storage }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-muted small">
                                            Biến thể: -
                                        </div>
                                    @endif

                                    @if($item->imeis && $item->imeis->isNotEmpty())
                                        <div class="text-muted small">
                                            IMEI:
                                            @foreach($item->imeis as $assignedImei)
                                                <span class="fw-semibold">
                                                    {{ $assignedImei->imei ?? $assignedImei->id }}
                                                </span>
                                                @if(!empty($assignedImei->status))
                                                    <span>({{ $assignedImei->status }})</span>
                                                @endif
                                                @if(!$loop->last)
                                                    <span>,</span>
                                                @endif
                                            @endforeach
                                        </div>

                                        @if(($item->product->product_type ?? null) === 'imei/serial' && !$item->hasFullImeiAssignment())
                                            <div class="text-danger small">
                                                Còn thiếu {{ $item->remainingImeiSlots() }} IMEI
                                            </div>
                                        @endif
                                    @elseif(($item->product->product_type ?? null) === 'imei/serial')
                                        <div class="text-danger small">
                                            Chưa gán IMEI
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </td>

                        <td>
                            @switch($order->status)
                                @case('pending')
                                    <span class="badge text-bg-secondary">Chờ xử lý</span>
                                    @break

                                @case('processing')
                                    <span class="badge text-bg-primary">Đang xử lý</span>
                                    @break

                                @case('shipping')
                                    <span class="badge text-bg-warning">Đang vận chuyển</span>
                                    @break

                                @case('completed')
                                    <span class="badge text-bg-success">Hoàn thành</span>
                                    @break

                                @case('cancelled')
                                    <span class="badge text-bg-danger">Đã hủy</span>
                                    @break

                                @case('returned')
                                    <span class="badge text-bg-info">Đã hoàn trả</span>
                                    @break

                                @default
                                    <span class="badge text-bg-light">{{ $order->status }}</span>
                            @endswitch
                        </td>

                        <td class="text-end fw-semibold">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </td>

                        <td>
                            @php
                                $paymentStatus = $order->payment->payment_status ?? null;

                                $paymentLabels = [
                                    'pending' => 'Chờ thanh toán',
                                    'paid' => 'Đã thanh toán',
                                    'failed' => 'Thất bại',
                                    'cancelled' => 'Đã hủy',
                                    'refunded' => 'Đã hoàn tiền',
                                ];

                                $paymentBadge = match($paymentStatus) {
                                    'paid' => 'text-bg-success',
                                    'pending' => 'text-bg-warning',
                                    'failed' => 'text-bg-danger',
                                    'cancelled' => 'text-bg-danger',
                                    'refunded' => 'text-bg-secondary',
                                    default => 'text-bg-light',
                                };
                            @endphp

                            <span class="badge {{ $paymentBadge }}">
                                {{ $paymentLabels[$paymentStatus] ?? 'Chưa có' }}
                            </span>
                        </td>

                        <td>
                            @php
                                $badge = match($order->fulfillment_status) {
                                    'pending' => 'text-bg-secondary',
                                    'waiting_pack' => 'text-bg-primary',
                                    'waiting_handover' => 'text-bg-info',
                                    'shipping' => 'text-bg-warning',
                                    'completed' => 'text-bg-success',
                                    'cancelled' => 'text-bg-danger',
                                    'failed' => 'text-bg-danger',
                                    default => 'text-bg-light',
                                };
                            @endphp

                            <span class="badge {{ $badge }}">
                                {{ $order->fulfillment_status_label }}
                            </span>
                        </td>

                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="btn btn-light btn-sm">
                                    Chi tiết
                                </a>

                                @php
                                    $missingRequiredImeis = $order->items->contains(function ($item) {
                                        return ($item->product->product_type ?? null) === 'imei/serial'
                                            && !$item->hasFullImeiAssignment();
                                    });
                                @endphp

                                @if($missingRequiredImeis)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            disabled
                                            title="Cần nhập IMEI trước khi in phiếu.">
                                        In phiếu
                                    </button>
                                @else
                                    <a href="{{ route('admin.orders.printShippingLabel', $order) }}"
                                    target="_blank"
                                    class="btn btn-outline-dark btn-sm">
                                        In phiếu
                                    </a>
                                @endif

                                @if($order->fulfillment_status === 'pending')
                                    <form action="{{ route('admin.orders.confirm', $order) }}" method="POST">
                                        @csrf

                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Xác nhận
                                        </button>
                                    </form>
                                @endif

                                @if($order->fulfillment_status === 'waiting_pack')
                                    <button type="button"
                                            class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#packedModal-{{ $order->id }}">
                                        Đã đóng gói
                                    </button>
                                @endif

                                @if($order->fulfillment_status === 'waiting_handover')
                                    <form action="{{ route('admin.orders.handover', $order) }}" method="POST">
                                        @csrf

                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Bắt đầu giao
                                        </button>
                                    </form>
                                @endif

                                @if($order->fulfillment_status === 'shipping')
                                    <button type="button"
                                            class="btn btn-success btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deliveredModal-{{ $order->id }}">
                                        Đã giao
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#failedModal-{{ $order->id }}">
                                        Giao thất bại
                                    </button>
                                @endif

                                @if($order->fulfillment_status === 'failed')
                                    <form action="{{ route('admin.orders.retryDelivery', $order) }}"
                                          method="POST"
                                          onsubmit="return confirm('Bạn có chắc muốn giao lại đơn hàng này không?');">
                                        @csrf

                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Giao lại
                                        </button>
                                    </form>
                                @endif

                                @if(!in_array($order->fulfillment_status, ['completed', 'cancelled'], true))
                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cancelModal-{{ $order->id }}">
                                        Hủy
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Không có đơn hàng nào
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="p-3">
            {{ $orders->links() }}
        </div>
    @endif
</section>

@foreach($orders as $order)

    @include('admin.orders.partials.packed-modal', [
        'order' => $order,
        'modalId' => 'packedModal-' . $order->id,
        'availableImeisByVariant' => $availableImeisByVariant,
    ])

    @include('admin.orders.partials.delivered-modal', [
        'order' => $order,
        'modalId' => 'deliveredModal-' . $order->id,
    ])

    <div class="modal fade"
         id="failedModal-{{ $order->id }}"
         tabindex="-1"
         aria-labelledby="failedModalLabel-{{ $order->id }}"
         aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.orders.markFailed', $order) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="failedModalLabel-{{ $order->id }}">
                        Xác nhận giao hàng thất bại
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Đóng">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <strong>Mã đơn:</strong> {{ $order->order_code }}
                    </div>

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
                                  placeholder="Ví dụ: Khách không nghe máy, khách hẹn giao lại, sai địa chỉ..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Đóng
                    </button>

                    <button type="submit"
                            class="btn btn-danger">
                        Xác nhận thất bại
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade"
     id="cancelModal-{{ $order->id }}"
     tabindex="-1"
     aria-labelledby="cancelModalLabel-{{ $order->id }}"
     aria-hidden="true">

    <div class="modal-dialog">

        <form action="{{ route('admin.orders.cancel', $order) }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content">

            @csrf

            <div class="modal-header">

                <h5 class="modal-title"
                    id="cancelModalLabel-{{ $order->id }}">

                    Hủy đơn hàng

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="mb-2">

                    <strong>Mã đơn:</strong>

                    {{ $order->order_code }}

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Lý do hủy <span class="text-danger">*</span>

                    </label>

                    <textarea
                        class="form-control"
                        name="cancel_reason"
                        rows="3"
                        required
                        placeholder="Nhập lý do hủy đơn..."></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Ảnh minh chứng (không bắt buộc)

                    </label>

                    <input
                        type="file"
                        class="form-control"
                        name="cancel_image"
                        accept="image/*">

                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                    Đóng

                </button>

                <button type="submit"
                        class="btn btn-danger">

                    Xác nhận hủy đơn

                </button>

            </div>

        </form>

    </div>

</div>

@endforeach

@endsection