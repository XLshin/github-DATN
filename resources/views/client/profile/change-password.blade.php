@extends('layouts.app')

@section('title', 'Đổi mật khẩu')

@section('header')
    <h1 class="h2 mb-1">Đổi mật khẩu</h1>
@endsection

@section('content')
    <div class="card shadow-sm border-0" style="max-width:520px">
        <div class="card-body">
            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" class="form-control" required>
                    @error('current_password')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Lưu mật khẩu</button>
            </form>
        </div>
    </div>
@endsection
