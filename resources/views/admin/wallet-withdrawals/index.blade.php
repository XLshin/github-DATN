@extends('layouts.admin')

@section('title', 'Rút tiền')
@section('page_icon', 'bi-arrow-down-circle')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Yêu cầu rút tiền')
@section('page_subtitle', 'Xử lý yêu cầu rút tiền về ngân hàng của khách hàng. SLA tối đa ' . \App\Models\WalletWithdrawal::MIN_PROCESSING_DAYS . ' ngày làm việc — admin có thể xác nhận ngay khi đã đủ căn cứ.')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="max-width:220px" onchange="this.form.submit()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
            </select>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Khách hàng</th>
                    <th>Số tiền</th>
                    <th>Tài khoản nhận</th>
                    <th>Trạng thái</th>
                    <th>SLA dự kiến</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $withdrawal->user->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $withdrawal->user->email ?? '' }}</div>
                        </td>
                        <td class="fw-semibold">{{ number_format((float) $withdrawal->amount, 0, ',', '.') }} đ</td>
                        <td>
                            {{ $withdrawal->bank_name }} — {{ $withdrawal->account_number }}
                            <div class="text-muted small">{{ $withdrawal->account_holder_name }}</div>
                        </td>
                        <td>
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
                        </td>
                        <td class="text-muted small">
                            @if($withdrawal->eligible_at && in_array($withdrawal->status, ['pending', 'processing'], true))
                                {{ $withdrawal->eligible_at->format('H:i d/m/Y') }}
                                @if($withdrawal->eligible_at->isFuture())
                                    <div class="text-danger">Còn {{ $withdrawal->eligible_at->diffForHumans(now(), true) }}</div>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.wallet-withdrawals.show', $withdrawal) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Không có yêu cầu rút tiền nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $withdrawals->links() }}
    </div>
</div>

@endsection
