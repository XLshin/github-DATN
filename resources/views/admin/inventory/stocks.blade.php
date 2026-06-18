<h1>Tồn kho</h1>

<a href="{{ route('inventory.index') }}">
    Quay lại
</a>

<hr>

<form method="GET">

    <input
        type="number"
        name="variant_id"
        value="{{ request('variant_id') }}"
        placeholder="Nhập Variant ID">

    <button type="submit">
        Tìm kiếm
    </button>

    <a href="{{ route('stocks') }}">
        Làm mới
    </a>

</form>

<hr>

<p>
    Tổng Variant:
    {{ $stocks->count() }}
</p>

<p>
    Hết hàng:
    {{ $stocks->where('total',0)->count() }}
</p>

<p>
    Sắp hết:
    {{ $stocks->filter(fn($item) => $item->total > 0 && $item->total < 5)->count() }}
</p>

<hr>

<table border="1" cellpadding="10">

    <tr>

        <th>Sản phẩm</th>

        <th>Màu sắc</th>

        <th>Dung lượng</th>

        <th>Tồn kho</th>

        <th>Trạng thái</th>

    </tr>

    @forelse($stocks as $stock)

        <tr>

            <td>
                {{ $stock->productVariant?->product?->name ?? 'N/A' }}
            </td>

            <td>
                {{ $stock->productVariant?->color ?? 'N/A' }}
            </td>

            <td>
                {{ $stock->productVariant?->storage ?? 'N/A' }}
            </td>

            <td>
                {{ $stock->total }}
            </td>

            <td>

                @if($stock->total <= 0)

                    Hết hàng

                @elseif($stock->total < 5)

                    Sắp hết

                @else

                    Còn hàng

                @endif

            </td>

        </tr>

    @empty

        <tr>

            <td colspan="5">
                Không có dữ liệu
            </td>

        </tr>

    @endforelse

</table>