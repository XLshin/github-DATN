@extends('layouts.auth')

@section('title', 'Quên mật khẩu')
@section('auth_subtitle', 'Khôi phục mật khẩu')

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <p class="eyebrow mb-1">Khôi phục</p>
            <h1 class="h3 mb-1">Quên mật khẩu</h1>
            <p class="text-muted mb-0">Nhập email hoặc SĐT đã đăng ký.</p>
        </div>

        <div class="mb-3">
            <label class="form-label" for="identifier">Email hoặc số điện thoại</label>
            <input class="form-control @error('identifier') is-invalid @enderror" id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" required>
            @error('identifier')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        @if (!empty($captcha['required']))
            <div class="mb-3">
                <label class="form-label" for="captcha">Xác minh: {{ $captcha['question'] }}</label>
                <input class="form-control @error('captcha') is-invalid @enderror" id="captcha" type="text" name="captcha" required>
                @error('captcha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        @endif

        <button class="btn btn-primary w-100" type="submit">Gửi hướng dẫn đặt lại</button>
    </form>
@endsection

@section('auth_footer')
    <a href="{{ route('login') }}">Quay lại đăng nhập</a>
@endsection
