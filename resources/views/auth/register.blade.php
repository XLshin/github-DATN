@extends('layouts.auth')

@section('title', 'Đăng ký')
@section('auth_subtitle', 'Tạo tài khoản Byte Zone Store')
@section('auth_heading', 'Tạo tài khoản mới')
@section('auth_description', 'Đăng ký để theo dõi đơn hàng, tích điểm và nhận ưu đãi từ Byte Zone Store.')

@section('content')
<form method="POST" action="{{ route('register.store') }}" class="needs-validation" novalidate>
    @csrf

    <div class="mb-3">
        <label class="form-label" for="name">Họ và tên</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-person"></i>
            </span>
            <input
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Nhập họ và tên"
                autocomplete="name"
                required>
        </div>
        @error('name')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-envelope"></i>
            </span>
            <input
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="example@email.com"
                autocomplete="email"
                required>
        </div>
        @error('email')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">Mật khẩu</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock"></i>
            </span>
            <input
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                type="password"
                name="password"
                placeholder="Tối thiểu 8 ký tự"
                autocomplete="new-password"
                required>
        </div>
        @error('password')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-shield-lock"></i>
            </span>
            <input
                class="form-control @error('password_confirmation') is-invalid @enderror"
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                placeholder="Nhập lại mật khẩu"
                autocomplete="new-password"
                required>
        </div>
        @error('password_confirmation')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <button class="btn btn-primary w-100" type="submit">
        <i class="bi bi-person-plus me-1"></i>
        Đăng ký
    </button>
</form>
@endsection

@section('auth_footer')
Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
@endsection