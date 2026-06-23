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
                    <label class="form-label">Họ tên</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Hủy</a>
            </form>
        </div>
    </div>
@endsection
