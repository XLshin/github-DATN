@extends('layouts.app')

@php
    $isCancelled = $order->status === 'cancelled';
@endphp

@section('title', $isCancelled ? 'Đơn hàng đã bị hủy' : 'Đặt hàng thành công')

@section('header')
    @if($isCancelled)
        <h1 class="h2 mb-1 text-danger"><i class="bi bi-x-circle-fill me-2"></i>Đơn hàng đã bị hủy</h1>
    @else
        <h1 class="h2 mb-1 text-success"><i class="bi bi-check-circle-fill me-2"></i>Đặt hàng thành công</h1>
    @endif
@endsection

@section('content')
@php
    $payment = $order->payment;
    $method  = $payment?->payment_method;
    $paid    = $payment?->payment_status === 'paid';

    $methodLabels = [
        'cod'           => 'Thanh toán khi nhận hàng (COD)',
        'card'          => 'Thẻ tín dụng/ghi nợ',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo'          => 'Ví MoMo',
        'vnpay'         => 'VNPAY',
    ];
@endphp

@if($isCancelled)
    <div class="alert alert-danger">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Đơn hàng này đã bị hủy</div>
        <div>{{ $order->cancel_reason ?? 'Không có lý do cụ thể.' }}</div>
        @if($order->cancelled_at)
            <div class="text-muted small mt-1">Hủy lúc: {{ $order->cancelled_at->format('H:i d/m/Y') }}</div>
        @endif
    </div>
@endif

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Thông tin đơn hàng</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:140px">Mã đơn hàng</td>
                        <td class="fw-bold">{{ $order->order_code }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tổng tiền</td>
                        <td class="fw-bold text-primary">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Giao đến</td>
                        <td>{{ $order->shipping_address }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Trạng thái thanh toán</h6>
                @if($isCancelled)
                    <div class="alert alert-secondary mb-2 py-2 small">
                        <i class="bi bi-slash-circle me-2"></i>Đơn hàng đã hủy — không cần thanh toán.
                    </div>
                @elseif($method === 'cod' && !$paid)
                    <div class="alert alert-info mb-2 py-2 small">
                        <i class="bi bi-truck me-2"></i>
                        <strong>COD</strong> — Thanh toán <strong>{{ number_format($order->total_amount,0,',','.') }} đ</strong>
                        khi nhận hàng.
                    </div>
                @elseif($method === 'bank_transfer' && !$paid)
                    <div class="alert alert-warning mb-2 py-2 small">
                        <i class="bi bi-hourglass-split me-2"></i>
                        <strong>Chờ xác minh</strong> — Chúng tôi sẽ xác nhận sau khi nhận được tiền chuyển khoản.
                        Nội dung CK: <code>{{ $order->order_code }}</code>
                    </div>
                    <a href="{{ route('checkout.payment', $order) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Xem lại thông tin CK
                    </a>
                @endif

                @if($paid)
                    <div class="alert alert-success mb-2 py-2">
                        <div class="fw-bold mb-2"><i class="bi bi-check-circle me-2"></i>Đã thanh toán</div>
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted ps-0" style="width:130px">Phương thức</td>
                                <td class="fw-semibold">{{ $methodLabels[$method] ?? strtoupper($method) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Số tiền</td>
                                <td class="fw-semibold">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                            </tr>
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
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('orders.show', $order) }}" class="btn btn-primary">
                <i class="bi bi-receipt me-1"></i>Xem chi tiết đơn hàng
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-shop me-1"></i>Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>

@if(!$isCancelled)
<script>
// Tự động phát hiện nếu đơn hàng bị hủy/đổi trạng thái sau khi trang này đã tải, để khách
// không tiếp tục thấy "Đặt hàng thành công" khi thực tế đơn đã bị hủy.
(function () {
    var statusCheckUrl = @json(route('orders.statusCheck', $order->id));
    var initialStatus = @json($order->status);
    var reloading = false;

    function poll() {
        if (reloading) return;

        fetch(statusCheckUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status !== initialStatus) {
                    reloading = true;
                    if (typeof window.showToast === 'function') {
                        window.showToast('Trạng thái đơn hàng vừa thay đổi. Đang tải lại...');
                    }
                    setTimeout(function () { window.location.reload(); }, 1500);
                }
            })
            .catch(function () {});
    }

    setInterval(poll, 15000);
})();
</script>
@endif
@endsection
