@extends('layouts.admin')

@section('title', 'Chi tiết yêu cầu rút tiền')
@section('page_icon', 'bi-arrow-down-circle')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Chi tiết yêu cầu rút tiền #' . $withdrawal->id)
@section('page_subtitle', 'Xử lý chuyển khoản và xác nhận hoàn tất yêu cầu rút tiền của khách hàng.')

@section('heading_actions')
<a href="{{ route('admin.wallet-withdrawals.index') }}" class="btn btn-light btn-sm">
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
            <div class="card-header bg-white fw-bold">Thông tin yêu cầu</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Số tiền rút</div>
                        <div class="fw-bold fs-5 text-primary">{{ number_format((float) $withdrawal->amount, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái</div>
                        <div>
                            @switch($withdrawal->status)
                                @case('completed')
                                    <span class="badge bg-success">Hoàn tất</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Từ chối</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-warning text-dark">Đang xử lý</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">Chờ xử lý</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Tài khoản nhận tiền (chốt tại thời điểm yêu cầu)</div>
                        <div class="fw-semibold">{{ $withdrawal->bank_name }} — {{ $withdrawal->account_number }}</div>
                        <div>{{ $withdrawal->account_holder_name }}</div>
                        @if($withdrawal->bankAccount)
                            <div class="mt-1">
                                @if($withdrawal->bankAccount->is_verified)
                                    <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i> Tài khoản đã xác minh</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Tài khoản chưa xác minh (bất thường)</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Yêu cầu lúc</div>
                        <div class="fw-semibold">{{ $withdrawal->requested_at->format('H:i:s d/m/Y') }}</div>
                    </div>
                    @if(in_array($withdrawal->status, ['pending', 'processing'], true))
                        <div class="col-md-6">
                            <div class="text-muted small">SLA dự kiến</div>
                            <div class="fw-semibold {{ $withdrawal->eligible_at?->isFuture() ? 'text-danger' : 'text-success' }}">
                                {{ $withdrawal->eligible_at?->format('H:i:s d/m/Y') ?? '—' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Dòng thời gian & Đối soát (Audit trail)</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="fw-semibold"><i class="bi bi-plus-circle text-primary me-1"></i> Yêu cầu được tạo, đã tạm giữ số dư</div>
                        <div class="text-muted small">{{ $withdrawal->requested_at->format('H:i:s d/m/Y') }} — khách hàng {{ $withdrawal->user->name ?? '-' }}</div>
                    </li>

                    @if($withdrawal->status === 'completed')
                        <li class="mb-3">
                            <div class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Đã hoàn tất chuyển khoản</div>
                            <div class="text-muted small">
                                {{ $withdrawal->completed_at?->format('H:i:s d/m/Y') }}
                                — admin: {{ $withdrawal->confirmedBy->name ?? '—' }}
                                — mã GD: {{ $withdrawal->transaction_code }}
                            </div>
                            @if($withdrawal->admin_note)
                                <div class="small mt-1">Ghi chú: {{ $withdrawal->admin_note }}</div>
                            @endif
                            @if($withdrawal->proof_image)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($withdrawal->proof_image) }}" target="_blank" class="d-inline-block mt-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($withdrawal->proof_image) }}"
                                         alt="Bằng chứng chuyển khoản" class="rounded border" style="max-height:160px">
                                </a>
                            @endif
                        </li>
                    @elseif($withdrawal->status === 'rejected')
                        <li class="mb-3">
                            <div class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i> Đã từ chối, hoàn lại số dư vào ví</div>
                            <div class="text-muted small">admin: {{ $withdrawal->rejectedBy->name ?? '—' }}</div>
                            <div class="small mt-1">Lý do: {{ $withdrawal->reject_reason }}</div>
                        </li>
                    @else
                        <li>
                            <div class="fw-semibold text-warning"><i class="bi bi-hourglass-split me-1"></i> Đang chờ đủ thời gian xử lý tối thiểu</div>
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
                <div class="mb-2"><strong>{{ $withdrawal->user->name ?? '-' }}</strong></div>
                <div class="text-muted small">{{ $withdrawal->user->email ?? '-' }}</div>
                <div class="text-muted small">{{ $withdrawal->user->phone ?? '-' }}</div>
                <hr>
                <div class="text-muted small">Số dư ví hiện tại</div>
                <div class="fw-bold">{{ number_format((float) ($withdrawal->user->wallet_balance ?? 0), 0, ',', '.') }} đ</div>
            </div>
        </div>

        @if(in_array($withdrawal->status, ['pending', 'processing'], true))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold text-success">Xác nhận đã chuyển khoản</div>
            <div class="card-body">
                @if($withdrawal->status === 'pending')
                    <form method="POST" action="{{ route('admin.wallet-withdrawals.processing', $withdrawal) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Chuyển sang đang xử lý</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.wallet-withdrawals.complete', $withdrawal) }}" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger small">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label small">Ảnh minh chứng đã chuyển khoản <span class="text-danger">*</span></label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                        <div class="form-text">Bắt buộc — chụp màn hình xác nhận chuyển khoản thành công từ ngân hàng/ví của bạn.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Ghi chú (tùy chọn)</label>
                        <textarea name="admin_note" class="form-control" rows="2" placeholder="VD: Mã giao dịch chuyển khoản nội bộ..."></textarea>
                    </div>
                    @if($withdrawal->eligible_at?->isFuture())
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i>
                            SLA dự kiến: {{ $withdrawal->eligible_at->format('H:i d/m/Y') }}. Bạn vẫn có thể xác nhận ngay nếu đã đủ căn cứ (ảnh bằng chứng hợp lệ).
                        </div>
                    @endif
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Xác nhận đã chuyển khoản {{ number_format((float) $withdrawal->amount, 0, ',', '.') }} đ cho khách hàng?')">
                        <i class="bi bi-check2-circle"></i> Xác nhận đã chuyển khoản
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold text-danger">Từ chối yêu cầu</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.wallet-withdrawals.reject', $withdrawal) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="reject_reason" class="form-control" rows="2" required
                                  placeholder="VD: Thông tin tài khoản không chính xác..."></textarea>
                    </div>
                    <div class="form-text mb-3">Số dư đã tạm giữ sẽ được hoàn lại vào ví khách hàng ngay khi từ chối.</div>
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Từ chối yêu cầu và hoàn lại số dư vào ví khách hàng?')">
                        <i class="bi bi-x-circle"></i> Từ chối & hoàn lại ví
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
