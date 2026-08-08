@extends('layouts.admin')

@section('title', 'Lịch sử giao dịch ngân hàng')
@section('page_icon', 'bi-journal-text')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Lịch sử giao dịch ngân hàng')
@section('page_subtitle', 'Toàn bộ giao dịch liên quan ngân hàng (nạp ví, rút tiền, hoàn tiền, thanh toán đơn hàng) gom về một nơi để tra cứu, đối soát.')

@section('content')

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Tìm kiếm</label>
                <input type="text" name="keyword" class="form-control form-control-sm"
                       placeholder="Tên/email khách, số TK, mã GD..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Loại giao dịch</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <option value="topup" {{ request('type') === 'topup' ? 'selected' : '' }}>Nạp ví</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Rút tiền</option>
                    <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Hoàn tiền</option>
                    <option value="order_payment" {{ request('type') === 'order_payment' ? 'selected' : '' }}>Thanh toán đơn hàng</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Chiều tiền</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <option value="in" {{ request('direction') === 'in' ? 'selected' : '' }}>Tiền vào</option>
                    <option value="out" {{ request('direction') === 'out' ? 'selected' : '' }}>Tiền ra</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại/Từ chối</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Từ ngày</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Lọc</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Thời gian</th>
                    <th>Loại</th>
                    <th>Khách hàng</th>
                    <th>Số tiền</th>
                    <th>Ngân hàng / Phương thức</th>
                    <th>Trạng thái</th>
                    <th>Người xử lý</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted small text-nowrap">{{ $log->occurred_at->format('H:i d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $log->direction === 'in' ? 'bg-success' : 'bg-danger' }}">
                                <i class="bi {{ $log->direction === 'in' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                            </span>
                            {{ $log->typeLabel() }}
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $log->user->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $log->user->email ?? '' }}</div>
                        </td>
                        <td class="fw-semibold text-nowrap">{{ number_format((float) $log->amount, 0, ',', '.') }} đ</td>
                        <td class="small">
                            {{ $log->methodLabel() }}
                            @if($log->bank_name)
                                <div class="text-muted">{{ $log->bank_name }} — {{ $log->account_number }}</div>
                            @endif
                        </td>
                        <td>
                            @switch($log->status)
                                @case('paid')
                                @case('completed')
                                    <span class="badge bg-success">{{ $log->status === 'paid' ? 'Đã thanh toán' : 'Hoàn tất' }}</span>
                                    @break
                                @case('failed')
                                @case('rejected')
                                    <span class="badge bg-danger">Thất bại/Từ chối</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-warning text-dark">Đang xử lý</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">Chờ xử lý</span>
                            @endswitch
                        </td>
                        <td class="text-muted small">{{ $log->handledBy->name ?? '—' }}</td>
                        <td class="text-muted small" style="max-width:220px">{{ $log->note }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Không có giao dịch nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $logs->links() }}
    </div>
</div>

@endsection
