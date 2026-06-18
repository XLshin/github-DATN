<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Orders</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;max-width:980px;margin:30px auto">
    <h1>My Orders</h1>
    @if($orders->isEmpty())
        <p>No orders yet.</p>
    @else
        <ul>
            @foreach($orders as $order)
                <li>
                    <a href="{{ route('orders.show', $order->id) }}">{{ $order->order_code }}</a>
                    - {{ $order->created_at->format('Y-m-d') }} - {{ number_format($order->total_amount,2) }}
                </li>
            @endforeach
        </ul>
        {{ $orders->links() }}
    @endif
    <p><a href="/">Back to home</a></p>
</body>
</html>
