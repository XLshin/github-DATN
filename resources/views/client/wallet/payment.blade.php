@extends('layouts.app')

@php
    $method  = $topup->payment_method;
    $amount  = $topup->amount;
    $code    = 'NAPVI' . str_pad((string) $topup->id, 6, '0', STR_PAD_LEFT);

    $expired     = $topup->payment_status === 'failed';
    $secondsLeft = $topup->expires_at ? max(0, (int) now()->diffInSeconds($topup->expires_at, false)) : 0;

    $bankId  = 'VCB';
    $acNo    = '1234567890';
    $info    = urlencode("Nap vi {$code}");
    $acName  = urlencode('BYTE ZONE STORE');
    $vietQr  = "https://img.vietqr.io/image/{$bankId}-{$acNo}-compact.jpg?amount={$amount}&addInfo={$info}&accountName={$acName}";

    $qrData  = urlencode("{$code}|{$amount}|" . strtoupper($method));
    $qrImg   = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={$qrData}";
@endphp

@section('title',
    match($method) {
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo'          => 'Nạp ví qua MoMo',
        'vnpay'         => 'Nạp ví qua VNPAY',
        'card'          => 'Nạp ví bằng thẻ',
        default         => 'Nạp tiền vào ví',
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

@if($expired)
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size:48px">⏰</div>
                <h5 class="fw-bold mb-2">Giao dịch đã hết hạn</h5>
                <p class="text-muted mb-4">
                    Phiên nạp tiền <code>{{ $code }}</code> đã quá thời gian quy định, bạn có thể thử lại.
                </p>
                <form method="POST" action="{{ route('wallet.topup.retry', $topup) }}">
                    @csrf
                    <button class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="bi bi-arrow-repeat me-2"></i>Thử lại nạp tiền
                    </button>
                </form>
                <a href="{{ route('wallet.index') }}" class="btn btn-link text-muted small">
                    Về trang ví
                </a>
            </div>
        </div>
    </div>
</div>
@else
@if($method === 'bank_transfer')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 d-flex align-items-center gap-2"
                 style="background:#007A4D;color:#fff">
                <span class="fs-5">🏦</span>
                <div>
                    <div class="fw-bold">Chuyển khoản ngân hàng</div>
                    <div class="small opacity-75">Vietcombank (VCB)</div>
                </div>
                <span class="ms-auto badge bg-white text-success">Đang chờ thanh toán</span>
            </div>
            <div class="card-body text-center py-4">
                <img src="{{ $vietQr }}" alt="QR VietQR" class="rounded border mb-3"
                     style="max-width:220px" onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($code . ' ' . $amount) }}'">
                <div class="text-muted small mb-4">Quét mã QR bằng app ngân hàng bất kỳ</div>

                <div class="text-start mx-auto" style="max-width:340px">
                    @foreach([
                        ['Ngân hàng', 'Vietcombank (VCB)'],
                        ['Số tài khoản', '1234 5678 90'],
                        ['Chủ tài khoản', 'BYTE ZONE STORE'],
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
                    <code>{{ $code }}</code> để chúng tôi xác nhận nhanh nhất.
                </div>

                <form method="POST" action="{{ route('wallet.topup.confirm', $topup) }}" enctype="multipart/form-data">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger small text-start">{{ $errors->first() }}</div>
                    @endif

                    <div class="text-start mb-3">
                        <label class="form-label small fw-semibold">
                            Ảnh chụp màn hình sao kê/biên lai chuyển khoản <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                        <div class="form-text">Bắt buộc để chúng tôi đối soát và xác nhận giao dịch của bạn.</div>
                    </div>

                    <button class="btn btn-success btn-lg w-100 mt-2">
                        <i class="bi bi-check2-circle me-2"></i>Tôi đã chuyển khoản
                    </button>
                </form>
                <a href="{{ route('wallet.index') }}" class="btn btn-link text-muted small mt-2 d-block">
                    Về trang ví
                </a>
            </div>
        </div>
    </div>
</div>

@elseif($method === 'momo')
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="text-center py-4" style="background:#AE2070;color:#fff">
                <div class="fw-bold fs-5 mb-1">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold me-1"
                          style="width:30px;height:30px;background:#fff;color:#AE2070;font-size:13px">M</span>
                    Ví MoMo
                </div>
                <div class="opacity-75 small">Quét mã QR để nạp tiền</div>
            </div>

            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <span class="badge rounded-pill px-3 py-2" style="background:#FFF0F5;color:#AE2070;font-size:.85rem">
                        <i class="bi bi-clock me-1"></i>Hết hạn sau: <span id="momo-timer" class="fw-bold">10:00</span>
                    </span>
                </div>

                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $qrImg }}" alt="QR MoMo" class="rounded border"
                         style="width:200px;height:200px">
                    <span class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                          style="width:34px;height:34px;background:#AE2070;font-size:12px;margin:4px">M</span>
                </div>

                <div class="fw-bold fs-4 mb-1" style="color:#AE2070">
                    {{ number_format($amount,0,',','.') }} đ
                </div>
                <div class="text-muted small mb-4">Mã giao dịch: <code>{{ $code }}</code></div>

                <div class="alert alert-light border text-start small">
                    <i class="bi bi-phone me-1"></i>
                    Mở app <strong>MoMo</strong> → <strong>Quét mã QR</strong> → Xác nhận nạp tiền
                </div>

                <form method="POST" action="{{ route('wallet.topup.confirm', $topup) }}" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger small text-start">{{ $errors->first() }}</div>
                    @endif
                    <div class="text-start mb-3">
                        <label class="form-label small fw-semibold">
                            Ảnh chụp màn hình xác nhận thanh toán MoMo <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                    </div>
                    <button class="btn btn-lg w-100 text-white fw-bold" style="background:#AE2070">
                        <i class="bi bi-check-circle me-2"></i>Xác nhận đã thanh toán MoMo
                    </button>
                </form>
                <a href="{{ route('wallet.index') }}" class="btn btn-link text-muted small mt-2 d-block">
                    Về trang ví
                </a>
            </div>
        </div>
    </div>
</div>

@elseif($method === 'vnpay')
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="text-center py-4" style="background:#005BAA;color:#fff">
                <div class="fw-bold fs-5 mb-1">VNPAY</div>
                <div class="opacity-75 small">Nạp tiền an toàn qua VNPAY</div>
            </div>

            <div class="card-body py-4 text-center">
                <div class="mb-2">
                    <span class="badge rounded-pill px-3 py-2" style="background:#EEF5FF;color:#005BAA;font-size:.85rem">
                        <i class="bi bi-clock me-1"></i>Hết hạn sau: <span id="vnpay-timer" class="fw-bold">10:00</span>
                    </span>
                </div>
                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $qrImg }}" alt="QR VNPAY" class="rounded border"
                         style="width:200px;height:200px">
                    <span class="position-absolute bottom-0 end-0 rounded fw-bold text-white px-1"
                          style="background:#005BAA;font-size:9px;margin:4px">VNPAY</span>
                </div>
                <div class="fw-bold fs-4 mb-1" style="color:#005BAA">
                    {{ number_format($amount,0,',','.') }} đ
                </div>
                <div class="text-muted small mb-3">Mã giao dịch: <code>{{ $code }}</code></div>
                <div class="alert alert-light border text-start small">
                    Mở app ngân hàng → <strong>Quét QR</strong> hoặc chọn <strong>VNPAY QR</strong>
                </div>

                <form method="POST" action="{{ route('wallet.topup.confirm', $topup) }}" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger small text-start">{{ $errors->first() }}</div>
                    @endif
                    <div class="text-start mb-3">
                        <label class="form-label small fw-semibold">
                            Ảnh chụp màn hình xác nhận thanh toán VNPAY <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                    </div>
                    <button class="btn btn-lg w-100 text-white fw-bold" style="background:#005BAA">
                        <i class="bi bi-check-circle me-2"></i>Xác nhận đã thanh toán VNPAY
                    </button>
                </form>
                <a href="{{ route('wallet.index') }}" class="btn btn-link text-muted small mt-2 d-block">
                    Về trang ví
                </a>
            </div>
        </div>
    </div>
</div>

@elseif($method === 'card')
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 fw-bold bg-dark text-white d-flex align-items-center gap-2">
                <i class="bi bi-credit-card-2-front fs-5"></i>
                Nạp ví bằng thẻ
                <span class="ms-auto d-flex gap-1">
                    <span class="badge bg-secondary" style="font-size:10px">VISA</span>
                    <span class="badge bg-danger" style="font-size:10px">MC</span>
                    <span class="badge bg-success" style="font-size:10px">JCB</span>
                </span>
            </div>
            <div class="card-body py-4">
                <div class="text-center mb-3">
                    <span class="badge rounded-pill px-3 py-2 bg-light text-dark border">
                        <i class="bi bi-clock me-1"></i>Phiên nạp tiền còn: <span id="card-timer" class="fw-bold">--:--</span>
                    </span>
                </div>

                <div class="rounded-3 p-4 mb-4 text-white position-relative overflow-hidden"
                     style="background:linear-gradient(135deg,#1a1a2e,#16213e);min-height:130px">
                    <div class="small opacity-75 mb-2">Số thẻ</div>
                    <div class="fs-5 fw-bold tracking-widest mb-3" id="card-preview-num">•••• •••• •••• ••••</div>
                    <div class="d-flex gap-4">
                        <div>
                            <div class="small opacity-75">Tên chủ thẻ</div>
                            <div class="fw-semibold text-uppercase" id="card-preview-name">HỌ TÊN</div>
                        </div>
                        <div>
                            <div class="small opacity-75">Hết hạn</div>
                            <div class="fw-semibold" id="card-preview-expiry">MM/YY</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Đây là môi trường thử nghiệm, dùng số thẻ hợp lệ theo chuẩn Luhn để test, ví dụ
                    <code>4111 1111 1111 1111</code>. Thẻ kết thúc bằng <code>0000</code> sẽ mô phỏng bị từ chối.
                </div>

                <form method="POST" action="{{ route('wallet.topup.confirm', $topup) }}" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger small">{{ $errors->first() }}</div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Ảnh chụp màn hình xác nhận giao dịch <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số thẻ</label>
                        <input type="text" name="card_number" id="card-number" class="form-control"
                               placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tên chủ thẻ</label>
                        <input type="text" name="card_name" id="card-name" class="form-control text-uppercase"
                               placeholder="NGUYEN VAN A" autocomplete="cc-name" required>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-7">
                            <label class="form-label small fw-semibold">Ngày hết hạn</label>
                            <input type="text" name="card_expiry" id="card-expiry" class="form-control"
                                   placeholder="MM/YY" maxlength="5" autocomplete="cc-exp" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold">CVV</label>
                            <input type="password" name="card_cvv" class="form-control"
                                   placeholder="•••" maxlength="4" autocomplete="cc-csc" required>
                        </div>
                    </div>

                    <button class="btn btn-dark btn-lg w-100">
                        <i class="bi bi-shield-lock me-2"></i>Nạp {{ number_format($amount,0,',','.') }} đ
                    </button>
                </form>
                <a href="{{ route('wallet.index') }}" class="btn btn-link text-muted small mt-2 d-block text-center">
                    ← Về trang ví
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
    startTimer('momo-timer', {{ $secondsLeft }});
    startTimer('vnpay-timer', {{ $secondsLeft }});
    startTimer('card-timer', {{ $secondsLeft }});

    const numInput    = document.getElementById('card-number');
    const nameInput   = document.getElementById('card-name');
    const expiryInput = document.getElementById('card-expiry');
    const previewNum  = document.getElementById('card-preview-num');
    const previewName = document.getElementById('card-preview-name');
    const previewExp  = document.getElementById('card-preview-expiry');

    numInput?.addEventListener('input', () => {
        let v = numInput.value.replace(/\D/g,'').substring(0,16);
        numInput.value = v.replace(/(.{4})/g,'$1 ').trim();
        previewNum.textContent = (numInput.value || '•••• •••• •••• ••••').padEnd(19,'•').substring(0,19);
    });

    nameInput?.addEventListener('input', () => {
        previewName.textContent = nameInput.value.toUpperCase() || 'HỌ TÊN';
    });

    expiryInput?.addEventListener('input', () => {
        let v = expiryInput.value.replace(/\D/g,'');
        if (v.length >= 2) v = v.substring(0,2)+'/'+v.substring(2,4);
        expiryInput.value = v;
        previewExp.textContent = expiryInput.value || 'MM/YY';
    });

    document.querySelectorAll('form[action*="topup"]').forEach(form => {
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
