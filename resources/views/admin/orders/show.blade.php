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
            <h5>Thông tin khách hàng</h5>
            <div>{{ $order->customer_phone }}</div>
            <div>{{ $order->shipping_address }}</div>
            <div>{{ $order->user->email ?? '-' }}</div>
        </div>
    </div>

    {{-- PRODUCTS --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5>Sản phẩm</h5>

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