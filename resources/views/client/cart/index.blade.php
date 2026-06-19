@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('header')
    <h1 class="h2 mb-1">Giỏ hàng</h1>
    <p class="text-muted mb-0">{{ $items->count() }} sản phẩm</p>
@endsection

@section('content')
    @if ($items->isEmpty())
        <div class="alert alert-info">Giỏ hàng trống. <a href="{{ route('home') }}">Tiếp tục mua sắm</a></div>
    @else
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">SL</th>
                            <th class="text-end">Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-end">{{ number_format($item->product->price, 0, ',', '.') }} đ</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->product->price * $item->quantity, 0, ',', '.') }} đ</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('cart.remove') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>
            <div class="text-end">
                <div class="fs-5 fw-bold">Tạm tính: {{ number_format($total, 0, ',', '.') }} đ</div>
                <a href="{{ route('checkout.show') }}" class="btn btn-primary mt-2">Thanh toán</a>
            </div>
        </div>
    @endif
@endsection
