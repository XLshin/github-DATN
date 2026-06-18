<h1>Tồn kho</h1>
<a href="{{ route('inventory.index') }}">
    quay lại
</a>
<table border="1">

<tr>
    <th>Variant ID</th>
    <th>Số lượng</th>
</tr>

@foreach($stocks as $stock)

<tr>
    <td>{{ $stock->product_variant_id }}</td>
    <td>{{ $stock->total }}</td>
</tr>

@endforeach

</table>