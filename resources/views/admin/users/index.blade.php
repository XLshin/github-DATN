@extends('layouts.admin')

@section('title', 'Người dùng')
@section('page_icon', 'bi-people')
@section('page_eyebrow', 'Hệ thống')
@section('page_title', 'Quản lý người dùng')
@section('page_subtitle', 'Danh sách tài khoản admin và khách hàng trong hệ thống.')

@section('heading_actions')
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tạo admin/nhân viên
        </a>
    @endif
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2 flex-grow-1 align-items-center">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    class="form-control form-control-sm table-search" placeholder="Tìm theo tên, email, SĐT...">

                <select name="role" class="form-select form-select-sm" style="max-width: 180px;">
                    <option value="all" @selected(($role ?? 'all') === 'all')>
                        Tất cả
                    </option>

                    <option value="customer" @selected(($role ?? 'all') === 'customer')>
                        Khách hàng
                    </option>

                    <option value="admin" @selected(($role ?? 'all') === 'admin')>
                        Admin
                    </option>

                    <option value="staff" @selected(($role ?? 'all') === 'staff')>
                        Nhân viên
                    </option>
                </select>

                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-funnel"></i>
                    Lọc
                </button>

                @if (!empty($search) || ($role ?? 'all') !== 'all')
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                        Đặt lại
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Vai trò</th>
                        <th>Hạng thành viên</th>
                        <th>Tổng chi tiêu</th>
                        <th>Đơn hàng</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>

                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>{{ $user->phone ?? '—' }}</td>

                            <td>
                                @if ($user->role === 'admin')
                                    <span class="badge text-bg-warning">
                                        Admin
                                    </span>
                                @elseif ($user->role === 'staff')
                                    <span class="badge text-bg-success">
                                        Nhân viên
                                    </span>
                                @else
                                    <span class="badge text-bg-primary">
                                        Khách hàng
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($user->role === 'customer')
                                    <span class="badge text-bg-info">
                                        {{ ucfirst($user->membership_level ?? 'bronze') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @if ($user->role === 'customer')
                                    {{ number_format($user->total_spent ?? 0, 0, ',', '.') }} đ
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @if ($user->role === 'customer')
                                    <span class="badge text-bg-light">
                                        {{ $user->orders_count ?? 0 }} đơn
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @if ($user->role === 'admin')
                                    <span class="badge text-bg-warning">
                                        Quản trị viên
                                    </span>
                                @elseif ($user->is_locked)
                                    <span class="badge text-bg-danger">
                                        Đã khóa
                                    </span>
                                @else
                                    <span class="badge text-bg-success">
                                        Hoạt động
                                    </span>
                                @endif
                            </td>

                            <td>{{ $user->created_at?->format('d/m/Y') }}</td>

                            <td class="text-end">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-eye"></i>
                                    Xem
                                </a>

                                @if (in_array($user->role, ['customer', 'staff'], true) && $user->id !== auth()->id())
                                    @php
                                        $roleLabel = $user->role === 'staff' ? 'nhân viên' : 'khách hàng';
                                    @endphp

                                    <form method="POST" action="{{ route('admin.users.toggle-lock', $user) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('{{ $user->is_locked ? 'Bạn có chắc muốn mở khóa tài khoản ' . $roleLabel . ' này?' : 'Bạn có chắc muốn khóa tài khoản ' . $roleLabel . ' này?' }}')">
                                        @csrf
                                        @method('PATCH')

                                        @if ($user->is_locked)
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-unlock"></i>
                                                Mở khóa
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-lock"></i>
                                                Khóa
                                            </button>
                                        @endif
                                    </form>
                                @else
                                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                        Không áp dụng
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Không tìm thấy người dùng
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-3">
                {{ $users->links() }}
            </div>
        @endif
    </section>
@endsection
