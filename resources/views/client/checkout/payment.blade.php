@extends('layouts.app')

@php
    $payment = $order->payment;
    $method  = $payment?->payment_method ?? 'cod';
    $amount  = $order->total_amount;
    $code    = $order->order_code;

    // Giao dịch qua cổng (vietqr) có phiên giới hạn thời gian giống thực tế
    $expired     = $payment?->payment_status === 'failed';
    $secondsLeft = $payment?->expires_at ? max(0, (int) now()->diffInSeconds($payment->expires_at, false)) : 0;

    $bankId  = config('services.sepay.bank_id');
    $acNo    = config('services.sepay.account_number');
    $info    = urlencode("Thanh toan {$code}");
    $acName  = urlencode((string) config('services.sepay.account_name'));
    $vietQr  = "https://img.vietqr.io/image/{$bankId}-{$acNo}-compact.jpg?amount={$amount}&addInfo={$info}&accountName={$acName}";
@endphp

@section('title',
    match($method) {
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'vietqr'        => 'Thanh toán VietQR',
        default         => 'Thanh toán',
    }
)

@section('content')

@if(session('error') || session('info'))
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show">
                <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>
</div>
@endif

@include('partials.client.receipt-modal', ['method' => $method, 'code' => $code])

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- GIAO DỊCH HẾT HẠN (vietqr)                                --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if($expired)
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                @if($payment->hasRetriesLeft())
                    <div class="mb-3" style="font-size:48px">⏰</div>
                    <h5 class="fw-bold mb-2">Giao dịch đã hết hạn</h5>
                    <p class="text-muted mb-4">
                        Phiên thanh toán cho đơn <code>{{ $code }}</code> đã quá thời gian quy định.
                        Sản phẩm đã được hoàn lại vào kho, bạn có thể thử lại
                        (lần {{ $payment->attempt_count + 1 }}/{{ \App\Models\Payment::MAX_ATTEMPTS }}).
                    </p>
                    <form method="POST" action="{{ route('checkout.payment.retry', $order) }}">
                        @csrf
                        <button class="btn btn-primary btn-lg w-100 mb-2">
                            <i class="bi bi-arrow-repeat me-2"></i>Thử lại thanh toán
                        </button>
                    </form>
                @else
                    <div class="mb-3" style="font-size:48px">🚫</div>
                    <h5 class="fw-bold mb-2">Đơn hàng đã bị hủy</h5>
                    <p class="text-muted mb-4">
                        Đơn <code>{{ $code }}</code> đã thất bại {{ \App\Models\Payment::MAX_ATTEMPTS }} lần thanh toán liên tiếp
                        nên đã tự động bị hủy. Vui lòng đặt lại đơn hàng mới nếu vẫn muốn mua.
                    </p>
                    <a href="{{ route('cart.index') }}" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="bi bi-cart me-2"></i>Về giỏ hàng
                    </a>
                @endif
                <a href="{{ route('checkout.success', $order) }}" class="btn btn-link text-muted small">
                    Xem chi tiết đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>
@else
{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- BANK TRANSFER                                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if($method === 'bank_transfer')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 d-flex align-items-center gap-2"
                 style="background:#007A4D;color:#fff">
                <span class="fs-5">🏦</span>
                <div>
                    <div class="fw-bold">Chuyển khoản ngân hàng</div>
                    <div class="small opacity-75">{{ config('services.sepay.bank_id') }}</div>
                </div>
                <span class="ms-auto badge bg-white text-success" id="bank_transfer-status-badge">
                    <span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem"></span>Đang chờ thanh toán
                </span>
            </div>
            <div class="card-body text-center py-4">
                {{-- QR --}}
                <img src="{{ $vietQr }}" alt="QR VietQR" class="rounded border mb-3"
                     style="max-width:220px" onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($code . ' ' . $amount) }}'">
                <div class="text-muted small mb-1">Quét mã QR bằng app ngân hàng bất kỳ</div>
                <div class="text-success small mb-4">
                    <i class="bi bi-lightning-charge-fill"></i> Hệ thống tự động xác nhận trong vài giây sau khi bạn chuyển khoản đúng nội dung
                </div>

                {{-- Bank details --}}
                <div class="text-start mx-auto" style="max-width:340px">
                    @foreach([
                        ['Ngân hàng', config('services.sepay.bank_id')],
                        ['Số tài khoản', config('services.sepay.account_number')],
                        ['Chủ tài khoản', config('services.sepay.account_name')],
                        ['Số tiền', number_format($amount,0,',','.').' đ'],
                        ['Nội dung CK', $code],
                    ] as [$label, $value])
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">{{ $label }}</span>
                        <span class="fw-semibold small text-end">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="alert alert-warning mt-4 text-start small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Vui lòng chuyển <strong>đúng số tiền</strong> và ghi <strong>đúng nội dung</strong>
                    <code>{{ $code }}</code> để hệ thống tự động đối chiếu và xác nhận.
                </div>

                <details class="text-start mt-3">
                    <summary class="small text-muted" style="cursor:pointer">
                        Đã chuyển khoản nhưng chưa thấy xác nhận sau vài phút? Gửi ảnh biên lai để được kiểm tra thủ công
                    </summary>
                    <form method="POST" action="{{ route('checkout.payment.confirm', $order) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        @if($errors->any())
                            <div class="alert alert-danger small text-start">{{ $errors->first() }}</div>
                        @endif
                        <div class="text-start mb-3">
                            <label class="form-label small fw-semibold">
                                Ảnh chụp màn hình sao kê/biên lai chuyển khoản <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                        </div>
                        <button class="btn btn-outline-success w-100">
                            <i class="bi bi-check2-circle me-2"></i>Gửi để đối soát thủ công
                        </button>
                    </form>
                </details>
                <a href="{{ route('checkout.success', $order) }}" class="btn btn-link text-muted small mt-3 d-block">
                    Xem đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- VIETQR (quét mã QR ngân hàng chuẩn VietQR, xác nhận qua webhook SePay thật) --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@elseif($method === 'vietqr')
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="text-center py-4" style="background:#00A0E3;color:#fff">
                <div class="fw-bold fs-5 mb-1">VietQR</div>
                <div class="opacity-75 small">Quét mã QR bằng app ngân hàng bất kỳ để thanh toán</div>
            </div>

            <div class="card-body text-center py-4">
                <div class="mb-3 d-flex justify-content-center gap-2 flex-wrap">
                    <span class="badge rounded-pill px-3 py-2" style="background:#E6F7FF;color:#00A0E3;font-size:.85rem">
                        <i class="bi bi-clock me-1"></i>Hết hạn sau: <span id="vietqr-timer" class="fw-bold">10:00</span>
                    </span>
                    <span class="badge rounded-pill px-3 py-2 bg-light text-dark border" id="vietqr-status-badge">
                        <span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem"></span>Đang chờ thanh toán
                    </span>
                </div>

                <img src="{{ $vietQr }}" alt="QR VietQR" class="rounded border mb-3"
                     style="max-width:220px" onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($code . ' ' . $amount) }}'">

                <div class="fw-bold fs-4 mb-1" style="color:#00A0E3">
                    {{ number_format($amount,0,',','.') }} đ
                </div>
                <div class="text-muted small mb-3">Mã đơn: <code>{{ $code }}</code></div>

                {{-- Bank details --}}
                <div class="text-start mx-auto mb-3" style="max-width:340px">
                    @foreach([
                        ['Ngân hàng', config('services.sepay.bank_id')],
                        ['Số tài khoản', config('services.sepay.account_number')],
                        ['Chủ tài khoản', config('services.sepay.account_name')],
                        ['Nội dung CK', $code],
                    ] as [$label, $value])
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">{{ $label }}</span>
                        <span class="fw-semibold small text-end">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="alert alert-light border text-start small mb-3">
                    <i class="bi bi-phone me-1"></i>
                    Mở app ngân hàng bất kỳ → <strong>Quét mã QR</strong> → Kiểm tra đúng số tiền và nội dung → Xác nhận. Hệ thống tự động ghi nhận trong vài giây sau khi bạn thanh toán.
                </div>

                <details class="text-start">
                    <summary class="small text-muted" style="cursor:pointer">
                        Đã thanh toán nhưng chưa thấy xác nhận sau vài phút? Gửi ảnh biên lai để được kiểm tra thủ công
                    </summary>
                    <form method="POST" action="{{ route('checkout.payment.confirm', $order) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        @if($errors->any())
                            <div class="alert alert-danger small text-start">{{ $errors->first() }}</div>
                        @endif
                        <div class="text-start mb-3">
                            <label class="form-label small fw-semibold">
                                Ảnh chụp màn hình xác nhận thanh toán <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                        </div>
                        <button class="btn w-100 text-white fw-bold" style="background:#00A0E3">
                            <i class="bi bi-check-circle me-2"></i>Gửi để đối soát thủ công
                        </button>
                    </form>
                </details>
                <a href="{{ route('checkout.success', $order) }}" class="btn btn-link text-muted small mt-3 d-block">
                    Xem đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>

@endif
@endif

@endsection

@push('scripts')
<script>
(function(){
    {{-- Hiện hóa đơn điện tử với tên người chuyển + tài khoản kinh doanh nhận tiền, giống VietQR thật --}}
    window.showReceipt = function (receipt, continueUrl) {
        if (!receipt) {
            window.location.href = continueUrl;
            return;
        }
        const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
        setText('receipt-payer-name', receipt.payer_name || '—');
        setText('receipt-business-account', receipt.business_account?.account_number || '—');
        setText('receipt-amount', Number(receipt.amount || 0).toLocaleString('vi-VN') + ' VND');
        setText('receipt-transaction-code', receipt.transaction_code || '—');
        setText('receipt-paid-at', receipt.paid_at || '—');

        const continueBtn = document.getElementById('receipt-continue-btn');
        continueBtn.href = continueUrl;

        const modalEl = document.getElementById('receiptModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.addEventListener('hidden.bs.modal', () => window.location.href = continueUrl, { once: true });
    };

    {{-- Timer đếm ngược khớp với expires_at thật của phiên giao dịch trên server --}}
    function startTimer(elId, seconds) {
        const el = document.getElementById(elId);
        if (!el) return;
        let secs = seconds;
        const iv = setInterval(() => {
            if (secs <= 0) {
                clearInterval(iv);
                el.textContent = '00:00';
                window.location.reload();
                return;
            }
            secs--;
            const m = String(Math.floor(secs/60)).padStart(2,'0');
            const s = String(secs%60).padStart(2,'0');
            el.textContent = m+':'+s;
        }, 1000);
    }
    startTimer('vietqr-timer', {{ $secondsLeft }});

    @if(in_array($method, ['bank_transfer', 'vietqr'], true) && ! $expired)
    {{-- Poll trạng thái thanh toán: bank_transfer/vietqr được webhook SePay thật xác nhận khi có
        tiền vào tài khoản. Trang này tự phát hiện thanh toán xong và chuyển sang trang thành công
        mà không cần khách thao tác gì thêm. --}}
    (function pollPaymentStatus(){
        const statusUrl = '{{ route('checkout.payment.status', $order) }}';
        const successUrl = '{{ route('checkout.success', $order) }}';
        const badge = document.getElementById('{{ $method }}-status-badge');

        const iv = setInterval(() => {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (data.paid) {
                        clearInterval(iv);
                        if (badge) badge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Đã xác nhận thanh toán!';
                        showReceipt(data.receipt, successUrl);
                    }
                })
                .catch(() => {});
        }, 4000);
    })();
    @endif

    {{-- Mô phỏng trạng thái "đang xử lý giao dịch" giống cổng thanh toán thật --}}
    document.querySelectorAll('form[action*="payment"]').forEach(form => {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"], button:not([type])');
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang xử lý giao dịch...';
        });
    });
})();
</script>
@endpush
