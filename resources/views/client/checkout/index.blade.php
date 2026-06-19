@extends('layouts.app')

@section('title', 'Thanh toán')

@section('header')
    <h1 class="h2 mb-1">Thanh toán</h1>
    <p class="text-muted mb-0">Hoàn tất thông tin giao hàng</p>
@endsection

@section('content')
    @if ($items->isEmpty())
        <div class="alert alert-warning">Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST" action="{{ route('checkout.process') }}" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Họ tên người nhận</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->user()->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', auth()->user()->phone) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ giao hàng</label>
                                <textarea name="shipping_address" class="form-control" rows="3" required>{{ old('shipping_address', auth()->user()->address) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phương thức thanh toán</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Đặt hàng</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Đơn hàng</div>
                    <ul class="list-group list-group-flush">
                        @foreach ($items as $item)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $item->product->name }} x{{ $item->quantity }}</span>
                                <span>{{ number_format($item->product->price * $item->quantity, 0, ',', '.') }} đ</span>
                            </li>
                        @endforeach
                        <li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Tổng cộng</span>
                            <span class="text-primary">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endsection
