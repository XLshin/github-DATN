@extends('layouts.admin')

@section('title', 'Người dùng')
@section('page_icon', 'bi-people')
@section('page_eyebrow', 'Hệ thống')
@section('page_title', 'Quản lý người dùng')
@section('page_subtitle', 'Danh sách tài khoản (US08).')

@section('heading_actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Thêm người dùng</a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2 flex-grow-1">
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm table-search" placeholder="Tìm theo tên, email, SĐT...">
                <button type="submit" class="btn btn-outline-primary btn-sm">Tìm</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>ID</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th>Ngày tạo</th><th class="text-end">Thao tác</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td><span class="badge {{ $user->role === 'admin' ? 'text-bg-warning' : 'text-bg-primary' }}">{{ $user->role === 'admin' ? 'Admin' : 'Khách hàng' }}</span></td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light btn-sm">Sửa</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Xóa người dùng?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Xóa</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Không tìm thấy người dùng</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="p-3">{{ $users->links() }}</div>@endif
    </section>
@endsection
