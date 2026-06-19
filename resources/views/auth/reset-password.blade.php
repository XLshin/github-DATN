@extends('layouts.auth')

@section('title', 'Đặt lại mật khẩu')
@section('auth_subtitle', 'Tạo mật khẩu mới')

@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <p class="eyebrow mb-1">Bảo mật</p>
            <h1 class="h3 mb-1">Đặt lại mật khẩu</h1>
            <p class="text-muted mb-0">Nhập mật khẩu mới cho tài khoản.</p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Mật khẩu mới</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        @error('token')<div class="alert alert-danger">{{ $message }}</div>@enderror

        <button class="btn btn-primary w-100" type="submit">Đặt lại mật khẩu</button>
    </form>
@endsection

@section('auth_footer')
    <a href="{{ route('login') }}">Quay lại đăng nhập</a>
@endsection
