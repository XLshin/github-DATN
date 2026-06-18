<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store - Home</title>
    <link rel="stylesheet" href="/build/assets/app.css" />
</head>
<body>
    <div style="max-width:980px;margin:40px auto;padding:0 16px;font-family:Arial,Helvetica,sans-serif">
        <h1>Product List</h1>
        <p><a href="{{ route('cart.index') }}">View cart</a></p>
        @if($products->isEmpty())
            <p>No products found.</p>
        @else
            <ul style="list-style:none;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">
                @foreach($products as $product)
                    <li style="border:1px solid #eee;padding:12px;border-radius:8px">
                        <h3 style="margin:0 0 8px 0">{{ $product->name }}</h3>
                        <p style="margin:0 0 8px 0;color:#666">{{ \Illuminate\Support\Str::limit($product->description, 120) }}</p>
                        <div style="font-weight:600">Price: {{ number_format($product->price,2) }} đ</div>
                        <form method="POST" action="{{ route('cart.add') }}" style="margin-top:8px">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="number" name="quantity" value="1" min="1" style="width:60px"> 
                            <button type="submit">Add to cart</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</body>
</html>
