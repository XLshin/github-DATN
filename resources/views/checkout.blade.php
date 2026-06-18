<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;max-width:900px;margin:30px auto">
    <h1>Checkout</h1>

    @if($cart == [])
        <p>Your cart is empty. <a href="/">Continue shopping</a></p>
    @else
        <form method="POST" action="{{ route('checkout.process') }}">
            @csrf
            <div>
                <label>Name</label><br>
                <input name="customer_name" value="" required>
            </div>
            <div>
                <label>Phone</label><br>
                <input name="customer_phone" value="" required>
            </div>
            <div>
                <label>Shipping address</label><br>
                <textarea name="shipping_address" required></textarea>
            </div>
            <div>
                <label>Payment method</label><br>
                <select name="payment_method">
                    <option value="cod">Cash on Delivery</option>
                </select>
            </div>
            <div style="margin-top:12px">
                <button type="submit">Place order ({{ number_format($total,2) }})</button>
            </div>
        </form>
    @endif

    <p><a href="{{ route('cart.index') }}">Back to cart</a></p>
</body>
</html>
