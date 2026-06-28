@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Giỏ hàng</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(empty($items))
        <p>Giỏ hàng trống.</p>
    @else
        <form method="post" action="{{ route('cart.update') }}">
            @csrf
            <table class="table">
                <thead><tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tổng</th><th></th></tr></thead>
                <tbody>
                @php $sum = 0; @endphp
                @foreach($items as $key => $it)
                    @php
                        $name = $it['product']->name ?? 'Sản phẩm';
                        $price = $it['price'] ?? 0;
                        $qty = $it['quantity'] ?? 0;
                        $total = $price * $qty;
                        $sum += $total;
                    @endphp
                    <tr>
                        <td>{{ $name }}</td>
                        <td>{{ number_format($price) }}</td>
                        <td>
                            <input type="number" name="quantity" value="{{ $qty }}" min="0" class="form-control" form="update-{{ $key }}">
                        </td>
                        <td>{{ number_format($total) }}</td>
                        <td>
                            <form id="update-{{ $key }}" method="post" action="{{ route('cart.update') }}">@csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="quantity" value="{{ $qty }}">
                                <button class="btn btn-sm btn-primary" type="submit">Cập nhật</button>
                            </form>
                            <form method="post" action="{{ route('cart.remove') }}">@csrf
                                <input type="hidden" name="key" value="{{ $key }}">
                                <button class="btn btn-sm btn-danger" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </form>

        <div class="text-right">
            <strong>Tổng: {{ number_format($sum) }}</strong>
            <a class="btn btn-success" href="{{ route('checkout.show') }}">Thanh toán</a>
        </div>
    @endif
</div>
@endsection
