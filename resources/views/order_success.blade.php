<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Success</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;max-width:900px;margin:30px auto">
    <h1>Order Placed</h1>
    <p>Order code: {{ $order->order_code }}</p>
    <p>Total: {{ number_format($order->total_amount,2) }}</p>
    <p>We will contact you at {{ $order->customer_phone }}</p>

    <p><a href="/">Back to home</a></p>
</body>
</html>
