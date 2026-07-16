@extends('layouts.admin')

@section('title', 'Hoàn tiền')
@section('page_icon', 'bi-arrow-return-left')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Yêu cầu hoàn tiền')
@section('page_subtitle', 'Xử lý hoàn tiền cho các đơn hàng đã thanh toán bị hủy. SLA hoàn qua ngân hàng: tối đa ' . \App\Models\RefundRequest::MIN_BANK_PROCESSING_DAYS . ' ngày — admin có thể xác nhận ngay khi đã đủ căn cứ.')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="method" class="form-select form-select-sm" style="max-width:200px" onchange="this.form.submit()">
                <option value="">-- Tất cả phương thức --</option>
                <option value="wallet" {{ request('method') === 'wallet' ? 'selected' : '' }}>Ví</option>
                <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Ngân hàng</option>
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:200px" onchange="this.form.submit()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
            </select>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Số tiền</th>
                    <th>Phương thức</th>
                    <th>Trạng thái</th>
                    <th>SLA dự kiến</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $refund)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $refund->order_id) }}">{{ $refund->order->order_code ?? '#' . $refund->order_id }}</a>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $refund->user->name ?? '-' }}</div>
                        </td>
                        <td class="fw-semibold">{{ number_format((float) $refund->amount, 0, ',', '.') }} đ</td>
                        <td>
                            @if($refund->method === 'wallet')
                                <span class="badge bg-primary">Ví ByteZone</span>
                            @else
                                <span class="badge bg-info text-dark">Ngân hàng</span>
                                <div class="text-muted small mt-1">
                                    {{ $refund->bank_name }} — {{ $refund->bank_account_number }}<br>
                                    {{ $refund->bank_account_name }}
                                </div>
                            @endif
                        </td>
                        <td>
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
                        </td>
                        <td class="text-muted small">
                            @if($refund->method === 'bank' && $refund->eligible_at && in_array($refund->status, ['pending', 'processing'], true))
                                {{ $refund->eligible_at->format('H:i d/m/Y') }}
                                @if($refund->eligible_at->isFuture())
                                    <div class="text-danger">Còn {{ $refund->eligible_at->diffForHumans(now(), true) }}</div>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Không có yêu cầu hoàn tiền nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $refunds->links() }}
    </div>
</div>

@endsection
