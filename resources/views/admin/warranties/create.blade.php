@extends('admin.layouts.app')

@section('content')
    <h1>Tạo phiếu bảo hành</h1>

    @if (session('error'))
        <p>{{ session('error') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.warranties.store') }}">
        @csrf

        <div>
            <label>Mã đơn hàng</label>
            <input type="text" name="order_code" value="{{ old('order_code') }}">
            @error('order_code')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>IMEI</label>
            <input type="text" name="imei" value="{{ old('imei') }}">
            @error('imei')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>Ngày bắt đầu</label>
            <input type="date" name="warranty_start" value="{{ old('warranty_start', now()->toDateString()) }}">
            @error('warranty_start')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>Ngày kết thúc</label>
            <input type="date" name="warranty_end" value="{{ old('warranty_end', now()->addYear()->toDateString()) }}">
            @error('warranty_end')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>Trạng thái</label>
            <select name="status">
                <option value="active" @selected(old('status') === 'active')>Còn bảo hành</option>
                <option value="claimed" @selected(old('status') === 'claimed')>Đang bảo hành</option>
                <option value="expired" @selected(old('status') === 'expired')>Hết hạn</option>
            </select>
        </div>

        <button type="submit">Tạo phiếu</button>
    </form>
@endsection