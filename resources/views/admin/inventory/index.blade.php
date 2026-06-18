<h1>Lịch sử kho</h1>

<a style="margin: 10px;" href="{{ route('inventory.create') }}">
    Nhập kho
</a>

<a style="margin: 10px;" href="{{ route('stocks') }}">
    Kiểm kho
</a>
<hr>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Variant</th>
        <th>Loại giao dịch</th>
        <th>Số lượng</th>
        <th>Ghi chú</th>
        <th>Hành động</th>
    </tr>

    @foreach($transactions as $item)

        <tr>
            <td>{{ $item->id }}</td>

            <td>
                {{ $item->product_variant_id }}
            </td>

            <td>
                {{ $item->type }}
            </td>

            <td>
                {{ $item->quantity }}
            </td>

            <td>
                {{ $item->note }}
            </td>
            <td>
                <a href="{{ route('inventory.edit', $item->id) }}">
                    Sửa
                </a>
        </tr>

    @endforeach

</table>