@extends('layouts.admin')

@section('title', 'Chi tiết yêu cầu hoàn tiền')
@section('page_icon', 'bi-arrow-return-left')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Chi tiết yêu cầu hoàn tiền #' . $refund->id)
@section('page_subtitle', 'Xử lý hoàn tiền cho đơn hàng đã thanh toán bị hủy.')

@section('heading_actions')
<a href="{{ route('admin.refunds.index') }}" class="btn btn-light btn-sm">
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
                        <div class="text-muted small">Đơn hàng</div>
                        <a href="{{ route('admin.orders.show', $refund->order_id) }}" class="fw-semibold">
                            {{ $refund->order->order_code ?? '#' . $refund->order_id }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Số tiền hoàn</div>
                        <div class="fw-bold fs-5 text-primary">{{ number_format((float) $refund->amount, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Phương thức</div>
                        <div>
                            @if($refund->method === 'wallet')
                                <span class="badge bg-primary">Ví ByteZone</span>
                            @else
                                <span class="badge bg-info text-dark">Ngân hàng</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái</div>
                        <div>
                            @switch($refund->status)
                                @case('completed')
                                    <span class="badge bg-success">Hoàn tất</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-warning text-dark">Đang xử lý</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">Từ chối</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">Chờ xử lý</span>
                            @endswitch
                        </div>
                    </div>

                    @if($refund->method === 'bank')
                        <div class="col-12">
                            <div class="text-muted small">Tài khoản nhận tiền</div>
                            <div class="fw-semibold">{{ $refund->bank_name }} — {{ $refund->bank_account_number }}</div>
                            <div>{{ $refund->bank_account_name }}</div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="text-muted small">Yêu cầu lúc</div>
                        <div class="fw-semibold">{{ $refund->requested_at->format('H:i:s d/m/Y') }}</div>
                    </div>
                    @if($refund->method === 'bank' && in_array($refund->status, ['pending', 'processing'], true))
                        <div class="col-md-6">
                            <div class="text-muted small">SLA dự kiến</div>
                            <div class="fw-semibold {{ $refund->eligible_at?->isFuture() ? 'text-danger' : 'text-success' }}">
                                {{ $refund->eligible_at?->format('H:i:s d/m/Y') ?? '—' }}
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
                        <div class="fw-semibold"><i class="bi bi-plus-circle text-primary me-1"></i> Yêu cầu được tạo</div>
                        <div class="text-muted small">{{ $refund->requested_at->format('H:i:s d/m/Y') }} — khách hàng {{ $refund->user->name ?? '-' }}</div>
                    </li>

                    @if($refund->status === 'completed')
                        <li class="mb-3">
                            <div class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Đã hoàn tất</div>
                            <div class="text-muted small">
                                {{ $refund->completed_at?->format('H:i:s d/m/Y') }}
                                @if($refund->method === 'bank')
                                    — hoàn tiền qua ngân hàng, admin đã xác nhận chuyển khoản
                                @else
                                    — hoàn tiền vào ví, xử lý tự động ngay khi tạo yêu cầu
                                @endif
                            </div>
                            @if($refund->admin_note)
                                <div class="small mt-1">Ghi chú: {{ $refund->admin_note }}</div>
                            @endif
                            @if($refund->proof_image)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($refund->proof_image) }}" target="_blank" class="d-inline-block mt-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($refund->proof_image) }}"
                                         alt="Bằng chứng chuyển khoản" class="rounded border" style="max-height:220px">
                                </a>
                            @endif
                        </li>
                    @elseif($refund->status === 'rejected')
                        <li class="mb-3">
                            <div class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i> Đã từ chối</div>
                        </li>
                    @else
                        <li>
                            <div class="fw-semibold text-warning"><i class="bi bi-hourglass-split me-1"></i> Đang chờ xử lý</div>
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
                <div class="mb-2"><strong>{{ $refund->user->name ?? '-' }}</strong></div>
                <div class="text-muted small">{{ $refund->user->email ?? '-' }}</div>
                <div class="text-muted small">{{ $refund->user->phone ?? '-' }}</div>
            </div>
        </div>

        @if($refund->method === 'bank' && $refund->status !== 'completed')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold text-success">Xác nhận đã chuyển khoản</div>
            <div class="card-body">
                @if($refund->status === 'pending')
                    <form method="POST" action="{{ route('admin.refunds.processing', $refund) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Chuyển sang đang xử lý</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.refunds.complete', $refund) }}" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger small">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label small">Ảnh minh chứng đã chuyển khoản <span class="text-danger">*</span></label>
                        <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                        <div class="form-text">Bắt buộc — chụp màn hình xác nhận chuyển khoản thành công.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Ghi chú (tùy chọn)</label>
                        <textarea name="admin_note" class="form-control" rows="2"></textarea>
                    </div>
                    @if($refund->eligible_at?->isFuture())
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i>
                            SLA dự kiến: {{ $refund->eligible_at->format('H:i d/m/Y') }}. Bạn vẫn có thể xác nhận ngay nếu đã đủ căn cứ (ảnh bằng chứng hợp lệ).
                        </div>
                    @endif
                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('Xác nhận đã chuyển khoản {{ number_format((float) $refund->amount, 0, ',', '.') }} đ cho khách hàng?')">
                        <i class="bi bi-check2-circle"></i> Xác nhận đã chuyển khoản
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
