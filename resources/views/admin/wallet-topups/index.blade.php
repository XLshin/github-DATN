@extends('layouts.admin')

@section('title', 'Nạp ví')
@section('page_icon', 'bi-wallet2')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Yêu cầu nạp ví')
@section('page_subtitle', 'Đối soát và xác nhận các giao dịch nạp tiền vào ví khách hàng.')

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
            <select name="status" class="form-select form-select-sm" style="max-width:200px" onchange="this.form.submit()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Đã cộng tiền</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại/Từ chối/Hết hạn</option>
            </select>
            <select name="method" class="form-select form-select-sm" style="max-width:200px" onchange="this.form.submit()">
                <option value="">-- Tất cả phương thức --</option>
                <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                <option value="momo" {{ request('method') === 'momo' ? 'selected' : '' }}>MoMo</option>
                <option value="vnpay" {{ request('method') === 'vnpay' ? 'selected' : '' }}>VNPAY</option>
                <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>Thẻ</option>
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
                    <th>Phương thức</th>
                    <th>Bằng chứng</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topups as $topup)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $topup->user->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $topup->user->email ?? '' }}</div>
                        </td>
                        <td class="fw-semibold">{{ number_format((float) $topup->amount, 0, ',', '.') }} đ</td>
                        <td>
                            @switch($topup->payment_method)
                                @case('bank_transfer') Chuyển khoản @break
                                @case('momo') MoMo @break
                                @case('vnpay') VNPAY @break
                                @case('card') Thẻ @break
                                @default {{ $topup->payment_method }}
                            @endswitch
                        </td>
                        <td>
                            @if($topup->proof_image)
                                <i class="bi bi-image text-success" title="Đã có ảnh bằng chứng"></i>
                            @else
                                <i class="bi bi-dash text-muted" title="Chưa có ảnh"></i>
                            @endif
                        </td>
                        <td>
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
                        </td>
                        <td class="text-muted small">{{ $topup->created_at->format('H:i d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.wallet-topups.show', $topup) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Không có yêu cầu nạp ví nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $topups->links() }}
    </div>
</div>

@endsection
