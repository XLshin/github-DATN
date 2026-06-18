@extends('admin.layouts.app')

@section('content')
    <h1>Tạo vận đơn</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p>{{ session('error') }}</p>
    @endif

    <p><strong>Mã đơn:</strong> {{ $order->order_code }}</p>
    <p><strong>Khách hàng:</strong> {{ $order->customer_name }}</p>
    <p><strong>SĐT:</strong> {{ $order->customer_phone }}</p>
    <p><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
    <p><strong>Trạng thái đơn:</strong> {{ $order->status }}</p>

    <form method="POST" action="{{ route('admin.shipments.storeFromOrder', $order) }}">
        @csrf

        <div>
            <label>Đơn vị vận chuyển</label>
            <input type="text" name="shipping_unit" value="{{ old('shipping_unit') }}">
            @error('shipping_unit')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>Mã vận đơn</label>
            <input type="text" name="tracking_code" value="{{ old('tracking_code') }}">
            @error('tracking_code')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">Tạo vận đơn</button>
    </form>
@endsection