@extends('layouts.admin')

@section('title', 'Sửa người dùng')
@section('page_icon', 'bi-person-gear')
@section('page_eyebrow', 'Quản lý người dùng')
@section('page_title', 'Sửa người dùng')
@section('page_subtitle', 'Cập nhật thông tin tài khoản, vai trò và mật khẩu người dùng.')

@section('heading_actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin người dùng</h5>
            <div class="text-muted small">
                Đang chỉnh sửa tài khoản: <strong>{{ $user->name }}</strong>
            </div>
        </div>
    </div>

    <div class="p-3">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" style="max-width: 900px;">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">
                        Họ và tên <span class="text-danger">*</span>
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Nhập họ và tên"
                        required>

                    @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">
                        Email <span class="text-danger">*</span>
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Nhập email"
                        required>

                    @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">
                        Số điện thoại
                    </label>

                    <input
                        id="phone"
                        type="text"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        class="form-control @error('phone') is-invalid @enderror"
                        placeholder="Nhập số điện thoại">

                    @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label">
                        Vai trò <span class="text-danger">*</span>
                    </label>

                    <select
                        id="role"
                        name="role"
                        class="form-select @error('role') is-invalid @enderror"
                        required>
                        <option value="customer" @selected(old('role', $user->role) === 'customer')>
                            Khách hàng
                        </option>

                        <option value="admin" @selected(old('role', $user->role) === 'admin')>
                            Quản trị viên
                        </option>
                    </select>

                    @error('role')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">
                        Địa chỉ
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="form-control @error('address') is-invalid @enderror"
                        placeholder="Nhập địa chỉ">{{ old('address', $user->address) }}</textarea>

                    @error('address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">
                        Mật khẩu mới
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Để trống nếu không đổi">

                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror

                    <div class="form-text">
                        Chỉ nhập nếu muốn thay đổi mật khẩu.
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">
                        Xác nhận mật khẩu mới
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Nhập lại mật khẩu mới">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Lưu thay đổi
                </button>

                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection