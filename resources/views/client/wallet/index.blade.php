@extends('layouts.app')

@section('title', 'Ví ByteZone')

@section('header')
    <h1 class="h2 mb-1">Ví ByteZone</h1>
    <p class="text-muted mb-0">Nạp tiền và quản lý số dư để thanh toán nhanh hơn</p>
@endsection

@push('styles')
<style>
    .wallet-balance-card {
        background: linear-gradient(135deg, #0d6efd, #0a58ca);
        color: #fff;
        border-radius: 16px;
        padding: 28px;
    }
    .wallet-balance-card .label { opacity: .85; font-size: .875rem; }
    .wallet-balance-card .amount { font-size: 2rem; font-weight: 700; }

    .checkout-section {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .checkout-section__title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 18px;
    }
    .checkout-section__badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #eef5ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .pm-list { display: flex; flex-direction: column; gap: 10px; }
    .pm-card {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e3e6ea;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }
    .pm-card:hover { border-color: #9fc2ff; background: #f8faff; }
    .pm-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: #f1f3f5;
        color: #495057;
    }
    .pm-icon--brand { color: #fff; font-weight: 700; font-size: 14px; text-align: center; }
    .pm-icon--bank { background: #e3f3ec; color: #1a9a6c; }
    .pm-icon--card { background: #f1f0ff; color: #5b4fd6; }
    .pm-text { flex: 1 1 auto; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .pm-title { font-weight: 600; }
    .pm-subtitle { font-size: .8125rem; color: #6c757d; }
    .pm-dot {
        width: 20px; height: 20px; border-radius: 50%;
        border: 2px solid #ced4da; flex-shrink: 0;
    }
    .pm-radio:checked + .pm-card {
        border-color: #0d6efd;
        background: #eef5ff;
        box-shadow: 0 0 0 1px #0d6efd inset;
    }
    .pm-radio:checked + .pm-card .pm-dot { border-color: #0d6efd; background: #0d6efd; }

    .wallet-tx-amount--in { color: #1a9a6c; font-weight: 600; }
    .wallet-tx-amount--out { color: #d9482b; font-weight: 600; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="wallet-balance-card mb-4">
            <div class="label">Số dư khả dụng</div>
            <div class="amount">{{ number_format((float) (auth()->user()->wallet_balance ?? 0), 0, ',', '.') }} đ</div>
        </div>

        <div class="checkout-section">
            <div class="checkout-section__title">
                <span class="checkout-section__badge"><i class="bi bi-plus-circle"></i></span>
                Nạp tiền vào ví
            </div>

            <form method="POST" action="{{ route('wallet.topup') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Số tiền muốn nạp</label>
                    <input type="number" name="amount" class="form-control" min="10000" step="1000" value="{{ old('amount', 100000) }}" required>
                    <div class="form-text">Tối thiểu 10.000 đ.</div>
                    @error('amount')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Phương thức nạp</label>
                    <div class="pm-list">
                        <input type="radio" class="pm-radio btn-check" name="payment_method" id="tm_bank" value="bank_transfer" checked>
                        <label class="pm-card" for="tm_bank">
                            <span class="pm-icon pm-icon--bank"><i class="bi bi-bank2"></i></span>
                            <span class="pm-text">
                                <span class="pm-title">Chuyển khoản ngân hàng</span>
                                <span class="pm-subtitle">Nhận thông tin tài khoản + QR sau khi xác nhận</span>
                            </span>
                            <span class="pm-dot"></span>
                        </label>

                        <input type="radio" class="pm-radio btn-check" name="payment_method" id="tm_momo" value="momo">
                        <label class="pm-card" for="tm_momo">
                            <span class="pm-icon pm-icon--brand" style="background:#AE2070">M</span>
                            <span class="pm-text">
                                <span class="pm-title">Ví MoMo</span>
                                <span class="pm-subtitle">Quét QR bằng app MoMo</span>
                            </span>
                            <span class="pm-dot"></span>
                        </label>

                        <input type="radio" class="pm-radio btn-check" name="payment_method" id="tm_vnpay" value="vnpay">
                        <label class="pm-card" for="tm_vnpay">
                            <span class="pm-icon pm-icon--brand" style="background:#005BAA;font-size:10px;line-height:1.1">VN<br>Pay</span>
                            <span class="pm-text">
                                <span class="pm-title">VNPAY</span>
                                <span class="pm-subtitle">Thanh toán qua ví VNPAY hoặc QR ngân hàng</span>
                            </span>
                            <span class="pm-dot"></span>
                        </label>

                        <input type="radio" class="pm-radio btn-check" name="payment_method" id="tm_card" value="card">
                        <label class="pm-card" for="tm_card">
                            <span class="pm-icon pm-icon--card"><i class="bi bi-credit-card-2-front"></i></span>
                            <span class="pm-text">
                                <span class="pm-title">Thẻ tín dụng / ghi nợ</span>
                                <span class="pm-subtitle">Visa, Mastercard, JCB</span>
                            </span>
                            <span class="pm-dot"></span>
                        </label>
                    </div>
                    @error('payment_method')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-wallet2 me-2"></i>Nạp tiền
                </button>
            </form>
        </div>

        <div class="checkout-section">
            <div class="checkout-section__title">
                <span class="checkout-section__badge"><i class="bi bi-bank2"></i></span>
                Tài khoản ngân hàng liên kết
            </div>

            @if($bankAccounts->isEmpty())
                <p class="text-muted">Bạn chưa liên kết tài khoản ngân hàng nào. Cần liên kết trước khi có thể rút tiền.</p>
            @else
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($bankAccounts as $account)
                        <div class="d-flex justify-content-between align-items-center border rounded p-3">
                            <div>
                                <div class="fw-semibold">
                                    {{ $account->bank_name }} — {{ $account->account_number }}
                                    @if($account->is_default)
                                        <span class="badge bg-primary ms-1">Mặc định</span>
                                    @endif
                                </div>
                                <div class="text-muted small">{{ $account->account_holder_name }}</div>
                                @if($account->is_verified)
                                    <span class="badge bg-success mt-1"><i class="bi bi-patch-check-fill"></i> Đã xác minh</span>
                                @else
                                    <span class="badge bg-warning text-dark mt-1"><i class="bi bi-exclamation-triangle"></i> Chưa xác minh — không thể rút tiền</span>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                                @if(!$account->is_default)
                                    <form method="POST" action="{{ route('bank-accounts.default', $account) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Đặt mặc định</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('bank-accounts.destroy', $account) }}"
                                      onsubmit="return confirm('Xóa liên kết tài khoản ngân hàng này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <button type="button" class="add-address-toggle w-100" id="add-bank-toggle"
                    style="display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border:1.5px dashed #ced4da;border-radius:12px;background:#fff;cursor:pointer;">
                <i class="bi bi-plus-circle"></i> Liên kết tài khoản ngân hàng mới
            </button>

            <div id="add-bank-form" class="d-none border rounded p-3 mt-3 bg-light">
                <form method="POST" action="{{ route('bank-accounts.store') }}">
                    @csrf
                    <div class="alert alert-warning small">
                        <i class="bi bi-shield-lock me-1"></i>
                        Vì lý do an toàn, tên chủ tài khoản phải khớp với tên tài khoản ByteZone (<strong>{{ auth()->user()->name }}</strong>)
                        để được tự động xác minh và rút tiền ngay. Tài khoản mang tên khác vẫn được lưu nhưng phải chờ admin xác minh thủ công.
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Ngân hàng</label>
                        <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="VD: Vietcombank" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Số tài khoản</label>
                        <input type="text" name="account_number" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Tên chủ tài khoản</label>
                        <input type="text" name="account_holder_name" class="form-control form-control-sm"
                               value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="bank_is_default">
                        <label class="form-check-label small" for="bank_is_default">Đặt làm mặc định</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Liên kết</button>
                        <button type="button" class="btn btn-light btn-sm" id="cancel-bank-form">Hủy</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="checkout-section">
            <div class="checkout-section__title">
                <span class="checkout-section__badge"><i class="bi bi-arrow-down-circle"></i></span>
                Rút tiền về ngân hàng
            </div>

            @php
                $verifiedAccounts = $bankAccounts->where('is_verified', true);
            @endphp

            @if($verifiedAccounts->isEmpty())
                <p class="text-muted mb-0">Bạn cần có ít nhất 1 tài khoản ngân hàng đã xác minh để rút tiền.</p>
            @else
                <form method="POST" action="{{ route('wallet.withdraw') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Rút về tài khoản</label>
                        <select name="bank_account_id" class="form-select" required>
                            @foreach($verifiedAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->bank_name }} — {{ $account->account_number }} ({{ $account->account_holder_name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Số tiền muốn rút</label>
                        <input type="number" name="amount" class="form-control" min="{{ \App\Models\WalletWithdrawal::MIN_AMOUNT }}" step="1000"
                               max="{{ (int) (auth()->user()->wallet_balance ?? 0) }}" required
                               oninvalid="this.setCustomValidity(this.validity.rangeOverflow ? 'Số tiền rút không được vượt quá số dư hiện có trong ví (' + Number(this.max).toLocaleString('vi-VN') + ' đ).' : (this.validity.rangeUnderflow ? 'Số tiền rút tối thiểu là ' + Number(this.min).toLocaleString('vi-VN') + ' đ.' : ''))"
                               oninput="this.setCustomValidity('')">
                        <div class="form-text">
                            Tối thiểu {{ number_format(\App\Models\WalletWithdrawal::MIN_AMOUNT, 0, ',', '.') }} đ.
                            Thời gian xử lý tối đa {{ \App\Models\WalletWithdrawal::MIN_PROCESSING_DAYS }} ngày làm việc kể từ khi yêu cầu được gửi.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100"
                            onclick="return confirm('Xác nhận yêu cầu rút tiền? Số tiền sẽ được tạm giữ ngay và chuyển về tài khoản ngân hàng trong tối đa {{ \App\Models\WalletWithdrawal::MIN_PROCESSING_DAYS }} ngày làm việc.')">
                        <i class="bi bi-send"></i> Gửi yêu cầu rút tiền
                    </button>
                </form>
            @endif

            @if($withdrawals->isNotEmpty())
                <hr>
                <div class="text-muted small fw-semibold mb-2">Yêu cầu gần đây</div>
                <div class="d-flex flex-column gap-2">
                    @foreach($withdrawals as $withdrawal)
                        <div class="d-flex justify-content-between align-items-center small border-bottom pb-2">
                            <div>
                                <div>{{ $withdrawal->bank_name }} — {{ $withdrawal->account_number }}</div>
                                <div class="text-muted">{{ $withdrawal->requested_at->format('H:i d/m/Y') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">{{ number_format((float) $withdrawal->amount, 0, ',', '.') }} đ</div>
                                @switch($withdrawal->status)
                                    @case('completed')
                                        <span class="badge bg-success">Đã chuyển</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Bị từ chối</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-warning text-dark">Đang xử lý</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">Chờ xử lý</span>
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="checkout-section">
            <div class="checkout-section__title">
                <span class="checkout-section__badge"><i class="bi bi-clock-history"></i></span>
                Lịch sử giao dịch ví
            </div>

            @if($transactions->isEmpty())
                <p class="text-muted mb-0">Chưa có giao dịch nào.</p>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($transactions as $tx)
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-2">
                            <div>
                                <div class="fw-semibold">{{ $tx->description }}</div>
                                <div class="text-muted small">{{ $tx->created_at->format('H:i d/m/Y') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="{{ $tx->amount >= 0 ? 'wallet-tx-amount--in' : 'wallet-tx-amount--out' }}">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format((float) $tx->amount, 0, ',', '.') }} đ
                                </div>
                                <div class="text-muted small">Số dư: {{ number_format((float) $tx->balance_after, 0, ',', '.') }} đ</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const toggleBtn = document.getElementById('add-bank-toggle');
    const form = document.getElementById('add-bank-form');
    const cancelBtn = document.getElementById('cancel-bank-form');

    toggleBtn?.addEventListener('click', () => {
        form.classList.remove('d-none');
        toggleBtn.classList.add('d-none');
    });

    cancelBtn?.addEventListener('click', () => {
        form.classList.add('d-none');
        toggleBtn.classList.remove('d-none');
    });
})();
</script>
@endpush

@endsection
