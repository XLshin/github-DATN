@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng')

@section('header')
    <h1 class="h2 mb-1">Đơn hàng {{ $order->order_code }}</h1>
    <p class="text-muted mb-0">{{ $order->created_at->format('d/m/Y H:i') }}</p>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Sản phẩm</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Tên</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Sản phẩm' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->total, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <p><strong>Trạng thái:</strong> {{ $order->status }}</p>
                    @if($order->buyer_type === 'proxy')
                        <p class="mb-1">
                            <strong>Người đặt mua:</strong> {{ $order->buyer_name }}
                            <span class="badge bg-info text-dark ms-1">Mua hộ</span>
                        </p>
                        <p><strong>SĐT người đặt:</strong> {{ $order->buyer_phone }}</p>
                    @endif
                    <p><strong>Người nhận:</strong> {{ $order->customer_name }}</p>
                    <p><strong>SĐT:</strong> {{ $order->customer_phone }}</p>
                    <p><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                    @if($order->shipment)
                    <hr>
                    <h6 class="mb-2">Vận đơn</h6>
                    <p class="mb-1"><strong>Đơn vị:</strong> {{ $order->shipment->shipping_unit ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Mã vận đơn:</strong>
                        @if($order->shipment->tracking_code)
                            <a href="{{ $order->shipment->tracking_url ?? '#' }}" target="_blank">{{ $order->shipment->tracking_code }}</a>
                        @else
                            Chưa có
                        @endif
                    </p>
                    <p class="mb-0"><strong>Trạng thái giao:</strong> {{ $order->shipment->shipping_status ?? 'Chưa có' }}</p>
                    @endif
                    <hr>
                    <p class="fs-5 fw-bold text-primary mb-0">Tổng: {{ number_format($order->total_amount, 0, ',', '.') }} đ</p>
                </div>
            </div>

            @php
                $payment = $order->payment;
                $methodLabels = [
                    'cod'           => 'Thanh toán khi nhận hàng (COD)',
                    'card'          => 'Thẻ tín dụng/ghi nợ',
                    'bank_transfer' => 'Chuyển khoản ngân hàng',
                    'momo'          => 'Ví MoMo',
                    'vnpay'         => 'VNPAY',
                ];
                $statusLabels = [
                    'pending'   => 'Chờ thanh toán',
                    'paid'      => 'Đã thanh toán',
                    'failed'    => 'Thanh toán thất bại',
                    'cancelled' => 'Đã hủy',
                    'refunded'  => 'Đã hoàn tiền',
                ];
                $statusClasses = [
                    'pending'   => 'bg-warning text-dark',
                    'paid'      => 'bg-success',
                    'failed'    => 'bg-danger',
                    'cancelled' => 'bg-danger',
                    'refunded'  => 'bg-secondary',
                ];
            @endphp

            @if($payment)
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="mb-3">Thông tin thanh toán</h6>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted ps-0" style="width:130px">Phương thức</td>
                                <td class="fw-semibold">{{ $methodLabels[$payment->payment_method] ?? strtoupper($payment->payment_method) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Trạng thái</td>
                                <td>
                                    <span class="badge {{ $statusClasses[$payment->payment_status] ?? 'bg-secondary' }}">
                                        {{ $statusLabels[$payment->payment_status] ?? $payment->payment_status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Số tiền</td>
                                <td class="fw-semibold">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                            </tr>
                            @if($payment->payment_status === 'paid')
                                <tr>
                                    <td class="text-muted ps-0">Người thanh toán</td>
                                    <td>
                                        {{ $payment->payer_name ?? $order->customer_name }}
                                        @if($payment->payer_note)
                                            <div class="text-muted">{{ $payment->payer_note }}</div>
                                        @endif
                                    </td>
                                </tr>
                                @if($payment->transaction_code)
                                    <tr>
                                        <td class="text-muted ps-0">Mã giao dịch</td>
                                        <td><code>{{ $payment->transaction_code }}</code></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted ps-0">Thời gian</td>
                                    <td>{{ $payment->paid_at?->format('H:i d/m/Y') }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
