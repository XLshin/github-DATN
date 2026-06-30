@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Chi tiết đơn hàng</h3>
            <div class="text-muted">
                Mã đơn: <strong>{{ $order->order_code }}</strong>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            <a href="{{ route('admin.orders.printShippingLabel', $order) }}" target="_blank" class="btn btn-primary">
                In phiếu
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

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

        $paymentLabels = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Hoàn tiền',
        ];

        $paymentStatus = $order->payment->payment_status ?? null;
    @endphp

    {{-- ORDER INFO --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <small class="text-muted">Trạng thái</small><br>
                    <span class="badge bg-primary">
                        {{ $fulfillmentLabels[$order->fulfillment_status] ?? $order->fulfillment_status }}
                    </span>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Thanh toán</small><br>
                    <span class="badge bg-success">
                        {{ $paymentLabels[$paymentStatus] ?? '-' }}
                    </span>
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Khách hàng</small><br>
                    {{ $order->customer_name }}
                </div>

                <div class="col-md-3">
                    <small class="text-muted">Tổng tiền</small><br>
                    <strong>{{ number_format($order->total_amount, 0, ',', '.') }} đ</strong>
                </div>

            </div>
        </div>
    </div>

    {{-- CUSTOMER --}}
    <div class="card mb-4">
        <div class="card-body">
<<<<<<< HEAD
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
                @if($order->status === 'pending')
                <span class="badge text-bg-secondary">Chờ xử lý</span>
                @elseif($order->status === 'processing')
                <span class="badge text-bg-primary">Đang xử lý</span>
                @elseif($order->status === 'completed')
                <span class="badge text-bg-success">Hoàn thành</span>
                @elseif($order->status === 'cancelled')
                <span class="badge text-bg-danger">Đã hủy</span>
                @elseif($order->status === 'shipping')
                <span class="badge text-bg-warning">Đang vận chuyển — không thể chỉnh sửa</span>
                @elseif($order->status === 'returned')
                <span class="badge text-bg-info">Đã hoàn trả</span>
                @else
                <span class="badge text-bg-light">
                    {{ $order->status }}
                </span>
                @endif

            </div>
=======
            <h5>Thông tin khách hàng</h5>
            <div>{{ $order->customer_phone }}</div>
            <div>{{ $order->shipping_address }}</div>
            <div>{{ $order->user->email ?? '-' }}</div>
>>>>>>> origin/main
        </div>
    </div>

    {{-- PRODUCTS --}}
    <div class="card mb-4">
        <div class="card-body">
<<<<<<< HEAD
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
                    
<!-- Customer Information Section -->
<section class="panel mb-4">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin khách hàng</h5>
            <div class="text-muted small">
                Chi tiết liên hệ và địa chỉ giao hàng.
            </div>
        </div>
    </div>

    <div class="p-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Tên khách hàng</div>
                <div class="fw-semibold">
                    {{ $order->customer_name ?? $order->user->name ?? 'N/A' }}
                </div>
            </div>

            <div class="col-md-6">
                <div class="text-muted small">Số điện thoại</div>
                <div class="fw-semibold">
                    {{ $order->customer_phone ?? 'N/A' }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="text-muted small">Địa chỉ giao hàng</div>
                <div class="fw-semibold">
                    {{ $order->shipping_address ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Sản phẩm trong đơn --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-1">Sản phẩm trong đơn</h5>
            <p class="text-muted mb-4">Danh sách sản phẩm, số lượng, biến thể và IMEI nếu có.</p>
=======
            <h5>Sản phẩm</h5>
>>>>>>> origin/main

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>IMEI</th>
                            <th class="text-end">SL</th>
                            <th class="text-end">Tiền</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>{{ $item->imei->imei ?? '-' }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">
                                    {{ number_format($item->total, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Tổng</th>
                            <th class="text-end">
                                {{ number_format($order->total_amount, 0, ',', '.') }} đ
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- PROOFS --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5>Ảnh minh chứng</h5>

            @forelse($order->proofs as $proof)
                <div class="border p-2 mb-2">
                    <img src="{{ asset('storage/' . $proof->image_path) }}" style="width:200px">
                    <div>{{ $proof->type }}</div>
                </div>
            @empty
                <div class="text-muted">Chưa có ảnh</div>
            @endforelse
        </div>
    </div>

    {{-- SHIPMENT --}}
    @if($order->shipment)
    <div class="card mb-4">
        <div class="card-body">
            <h5>Hình ảnh giao hàng</h5>

            <div class="row">
                <div class="col-md-6">
                    <img src="{{ asset('storage/'.$order->shipment->shipped_image) }}" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <img src="{{ asset('storage/'.$order->shipment->delivered_image) }}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection