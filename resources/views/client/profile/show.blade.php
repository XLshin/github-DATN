@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('header')
<h1 class="h2 mb-1">Thông tin cá nhân</h1>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Họ tên</dt>
            <dd class="col-sm-9">{{ $user->name }}</dd>
            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9">{{ $user->email }}</dd>
            <dt class="col-sm-3">SĐT</dt>
            <dd class="col-sm-9">{{ $user->phone ?? '—' }}</dd>
            {{-- Địa chỉ đã chuyển sang quản lý riêng, không hiển thị ở đây nữa --}}
            <dt class="col-sm-3">Hạng thành viên</dt>
            <dd class="col-sm-9">{{ $user->membership_level ?? 'bronze' }}</dd>
        </dl>
        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">Chỉnh sửa thông tin</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Quay lại Dashboard</a>
        </div>
    </div>
</div>
<div class="mt-3">
    <a href="{{ route('dashboard') }}#addresses" class="btn btn-link">→ Quản lý địa chỉ nhận hàng</a>
</div>
@endsection