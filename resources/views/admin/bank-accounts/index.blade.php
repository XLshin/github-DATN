@extends('layouts.admin')

@section('title', 'Tài khoản ngân hàng')
@section('page_icon', 'bi-bank2')
@section('page_eyebrow', 'Quản lý bán hàng')
@section('page_title', 'Tài khoản ngân hàng khách hàng')
@section('page_subtitle', 'Xác minh thủ công các tài khoản ngân hàng có tên chủ TK không khớp tự động trước khi khách được phép rút tiền về.')

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
                <option value="">-- Tất cả --</option>
                <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Chưa xác minh</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Đã xác minh</option>
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
                    <th>Ngân hàng</th>
                    <th>Số tài khoản</th>
                    <th>Tên chủ TK</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bankAccounts as $account)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $account->user->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $account->user->email ?? '' }}</div>
                        </td>
                        <td>{{ $account->bank_name }}</td>
                        <td>{{ $account->account_number }}</td>
                        <td>
                            {{ $account->account_holder_name }}
                            @if(!\App\Models\BankAccount::namesMatch($account->account_holder_name, $account->user->name ?? ''))
                                <div class="text-warning small"><i class="bi bi-exclamation-triangle"></i> Tên không khớp tài khoản đăng ký</div>
                            @endif
                        </td>
                        <td>
                            @if($account->is_verified)
                                <span class="badge bg-success">Đã xác minh</span>
                                @if($account->verifiedBy)
                                    <div class="text-muted small">bởi {{ $account->verifiedBy->name }}</div>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">Chưa xác minh</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(!$account->is_verified)
                                <form method="POST" action="{{ route('admin.bank-accounts.verify', $account) }}"
                                      onsubmit="return confirm('Xác nhận đã kiểm tra và xác minh tài khoản ngân hàng này thuộc sở hữu của khách hàng?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-patch-check"></i> Xác minh
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có tài khoản ngân hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $bankAccounts->links() }}
    </div>
</div>

@endsection
