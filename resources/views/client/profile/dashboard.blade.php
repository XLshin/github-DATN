@extends('layouts.app')

@section('title', 'Tài khoản')

@section('header')
    <h1 class="h2 mb-1">Xin chào, {{ auth()->user()->name }}</h1>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100"><div class="card-body">
                <div class="text-muted small">Email</div>
                <div class="fw-semibold">{{ auth()->user()->email }}</div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100"><div class="card-body">
                <div class="text-muted small">Vai trò</div>
                <div class="fw-semibold">{{ auth()->user()->isAdmin() ? 'Quản trị viên' : 'Khách hàng' }}</div>
            </div></div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('profile.show') }}" class="btn btn-primary">Thông tin cá nhân</a>
        <a href="{{ route('password.change') }}" class="btn btn-outline-primary">Đổi mật khẩu</a>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Đơn hàng</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning">Trang quản trị</a>
        @endif
    </div>
@endsection
