@extends('layouts.admin')

@section('title', 'Quản lý điểm')
@section('page_icon', 'bi-star')
@section('page_eyebrow', 'Quản lý thành viên')
@section('page_title', 'Quản lý điểm tích lũy')
@section('page_subtitle', 'Xem và quản lý điểm tích lũy của khách hàng.')

@section('content')
    <section class="panel">
        <header class="panel-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="panel-title">Danh sách khách hàng</h3>
            </div>
            <form method="GET" action="{{ route('admin.points.index') }}" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Tìm tên, email, phone..." value="{{ request('search') }}" />
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>
        </header>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên khách hàng</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th class="text-center">Điểm tích lũy</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge text-bg-success fs-6">
                                {{ number_format($user->points) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.points.show', $user) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Không tìm thấy khách hàng nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users, 'hasPages') && $users->hasPages())
        <div class="p-3">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </section>
@endsection
