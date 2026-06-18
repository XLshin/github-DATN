<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;max-width:900px;margin:30px auto">
    <h1>Your Cart</h1>

    @if(session('success'))
        <div style="padding:8px;background:#e6ffed;border:1px solid #b8f5c9">{{ session('success') }}</div>
    @endif

    @if(empty($cart))
        <p>No items in cart.</p>
    @else
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th align="left">Product</th>
                    <th align="right">Price</th>
                    <th align="right">Qty</th>
                    <th align="right">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td align="right">{{ number_format($item['price'],2) }}</td>
                        <td align="right">{{ $item['quantity'] }}</td>
                        <td align="right">{{ number_format($item['price'] * $item['quantity'],2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                <button type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="text-align:right;font-weight:600">Subtotal: {{ number_format($total,2) }}</p>
        <p style="text-align:right"><a href="{{ route('checkout.show') }}">Proceed to checkout</a></p>
    @endif

    <p><a href="/">Back to home</a></p>
</body>
</html>
