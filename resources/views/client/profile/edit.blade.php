@extends('layouts.app')

@section('title', 'Cập nhật thông tin')

@section('header')
<h1 class="h2 mb-1">Cập nhật thông tin</h1>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                    value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control"
                    value="{{ old('phone', $user->phone) }}" required>
                @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Hủy</a>
        </form>
        <p class="text-muted small mt-2">
            Để thay đổi địa chỉ nhận hàng, vui lòng vào
            <a href="{{ route('dashboard') }}">Trang quản lý địa chỉ</a>.
        </p>
    </div>
</div>
@endsection