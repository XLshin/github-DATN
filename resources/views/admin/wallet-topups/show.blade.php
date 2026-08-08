@extends('layouts.admin')

@section('title', 'Chi tiết yêu cầu nạp ví')
@section('page_icon', 'bi-wallet2')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Chi tiết yêu cầu nạp ví #' . $topup->id)
@section('page_subtitle', 'Đối soát và xử lý giao dịch nạp tiền vào ví khách hàng.')

@section('heading_actions')
<a href="{{ route('admin.wallet-topups.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Danh sách
</a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Thông tin giao dịch</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Mã tham chiếu</div>
                        <div class="fw-semibold">{{ $topup->referenceCode() }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Số tiền yêu cầu</div>
                        <div class="fw-bold fs-5 text-primary">{{ number_format((float) $topup->amount, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Phương thức</div>
                        <div class="fw-semibold">
                            @switch($topup->payment_method)
                                @case('bank_transfer') Chuyển khoản ngân hàng @break
                                @case('momo') Ví MoMo @break
                                @case('vnpay') VNPAY @break
                                @case('card') Thẻ tín dụng/ghi nợ @break
                                @default {{ $topup->payment_method }}
                            @endswitch
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái</div>
                        <div>
                            @switch($topup->payment_status)
                                @case('paid')
                                    <span class="badge bg-success">Đã cộng tiền</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-secondary">Thất bại/Từ chối/Hết hạn</span>
                                    @break
                                @default
                                    <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Mã giao dịch hệ thống</div>
                        <div class="fw-semibold">{{ $topup->transaction_code ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tên người chuyển (khách khai báo)</div>
                        <div class="fw-semibold">{{ $topup->payer_name ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Ghi chú từ khách</div>
                        <div>{{ $topup->payer_note ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Ảnh bằng chứng chuyển khoản</div>
            <div class="card-body text-center">
                @if($topup->proof_image)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($topup->proof_image) }}" target="_blank">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($topup->proof_image) }}"
                             alt="Bằng chứng chuyển khoản" class="img-fluid rounded border" style="max-height:420px">
                    </a>
                    <div class="text-muted small mt-2">Nhấn vào ảnh để xem cỡ đầy đủ</div>
                @else
                    <p class="text-muted mb-0">Khách chưa gửi ảnh bằng chứng.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Dòng thời gian & Đối soát (Audit trail)</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="fw-semibold"><i class="bi bi-plus-circle text-primary me-1"></i> Yêu cầu được tạo</div>
                        <div class="text-muted small">{{ $topup->created_at->format('H:i:s d/m/Y') }} — khách hàng {{ $topup->user->name ?? '-' }}</div>
                    </li>

                    @if($topup->payment_status === 'paid')
                        <li class="mb-3">
                            <div class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Đã xác nhận & cộng tiền</div>
                            <div class="text-muted small">
                                {{ $topup->paid_at?->format('H:i:s d/m/Y') }}
                                — admin: {{ $topup->confirmedBy->name ?? '—' }}
                            </div>
                            @if($topup->admin_note)
                                <div class="small mt-1">Ghi chú admin: {{ $topup->admin_note }}</div>
                            @endif
                        </li>
                    @elseif($topup->payment_status === 'failed' && $topup->rejected_by)
                        <li class="mb-3">
                            <div class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i> Đã từ chối</div>
                            <div class="text-muted small">
                                admin: {{ $topup->rejectedBy->name ?? '—' }}
                            </div>
                            <div class="small mt-1">Lý do: {{ $topup->reject_reason }}</div>
                        </li>
                    @elseif($topup->payment_status === 'failed')
                        <li class="mb-3">
                            <div class="fw-semibold text-secondary"><i class="bi bi-clock-history me-1"></i> Hết hạn</div>
                            <div class="text-muted small">Giao dịch quá thời gian quy định mà khách chưa xác nhận.</div>
                        </li>
                    @else
                        <li>
                            <div class="fw-semibold text-warning"><i class="bi bi-hourglass-split me-1"></i> Đang chờ admin đối soát</div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Khách hàng</div>
            <div class="card-body">
                <div class="mb-2"><strong>{{ $topup->user->name ?? '-' }}</strong></div>
                <div class="text-muted small">{{ $topup->user->email ?? '-' }}</div>
                <div class="text-muted small">{{ $topup->user->phone ?? '-' }}</div>
                <hr>
                <div class="text-muted small">Số dư ví hiện tại</div>
                <div class="fw-bold">{{ number_format((float) ($topup->user->wallet_balance ?? 0), 0, ',', '.') }} đ</div>
            </div>
        </div>

        @if($topup->payment_status === 'pending')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold text-success">Xác nhận đã nhận tiền</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.wallet-topups.confirm', $topup) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Số tiền thực nhận <span class="text-danger">*</span></label>
                        <input type="number" name="confirmed_amount" class="form-control" step="1"
                               value="{{ old('confirmed_amount', (int) $topup->amount) }}" required>
                        <div class="form-text">Phải khớp đúng số tiền yêu cầu ({{ number_format((float) $topup->amount, 0, ',', '.') }} đ) để hệ thống chấp nhận.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Ghi chú (tùy chọn)</label>
                        <textarea name="admin_note" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Xác nhận đã nhận đủ tiền và cộng vào ví khách hàng?')">
                        <i class="bi bi-check2-circle"></i> Xác nhận & cộng tiền
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold text-danger">Từ chối yêu cầu</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.wallet-topups.reject', $topup) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="reject_reason" class="form-control" rows="2" required
                                  placeholder="VD: Không nhận được tiền, sai số tiền, sai nội dung chuyển khoản..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Xác nhận từ chối yêu cầu nạp ví này?')">
                        <i class="bi bi-x-circle"></i> Từ chối
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
