@extends('layouts.auth')

@section('title', 'Đăng nhập')
@section('auth_subtitle', 'Đăng nhập tài khoản Byte Zone Store')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="needs-validation" novalidate>
        @csrf
        <div class="mb-4">
            <p class="eyebrow mb-1">Xác thực</p>
            <h1 class="h3 mb-1">Đăng nhập</h1>
            <p class="text-muted mb-0">Vui lòng nhập email và mật khẩu.</p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Mật khẩu</label>
                <a class="small fw-semibold" href="{{ route('password.request') }}">Quên mật khẩu?</a>
            </div>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
        </div>

        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</button>
    </form>
@endsection

@section('auth_footer')
    Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
@endsection
