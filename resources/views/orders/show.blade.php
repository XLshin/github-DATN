<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Order {{ $order->order_code }}</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;max-width:980px;margin:30px auto">
    <h1>Order {{ $order->order_code }}</h1>
    <p>Status: {{ $order->status }}</p>
    <p>Placed: {{ $order->created_at }}</p>
    <h3>Items</h3>
    <ul>
        @foreach($order->items as $item)
            <li>{{ $item->product->name ?? 'Product #'.$item->product_id }} x {{ $item->quantity }} — {{ number_format($item->total,2) }}</li>
        @endforeach
    </ul>
    <p>Total: {{ number_format($order->total_amount,2) }}</p>
    <p><a href="{{ route('orders.index') }}">Back to orders</a></p>
</body>
</html>
