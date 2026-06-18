<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Orders</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;max-width:980px;margin:30px auto">
    <h1>All Orders (Admin)</h1>
    <ul>
        @foreach($orders as $order)
            <li>
                <a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_code }}</a>
                - {{ $order->user->name ?? 'Guest' }} - {{ number_format($order->total_amount,2) }}
            </li>
        @endforeach
    </ul>
    {{ $orders->links() }}
    <p><a href="/">Back to home</a></p>
</body>
</html>
