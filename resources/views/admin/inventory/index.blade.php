<h1>Lịch sử kho</h1>

<div style="margin-bottom:20px">

    <a href="{{ route('inventory.create') }}">
        Nhập kho
    </a>

    |

    <a href="{{ route('stocks') }}">
        Kiểm kho
    </a>

</div>

<hr>

<form method="GET">

    <input
        type="text"
        name="keyword"
        value="{{ request('keyword') }}"
        placeholder="Nhập Variant ID">

    <select name="transaction_type">

        <option value="">
            Tất cả giao dịch
        </option>

        <option
            value="import"
            {{ request('transaction_type') == 'import' ? 'selected' : '' }}>
            Nhập kho
        </option>

        <option
            value="export"
            {{ request('transaction_type') == 'export' ? 'selected' : '' }}>
            Xuất kho
        </option>

        <option
            value="adjustment"
            {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>
            Điều chỉnh
        </option>

    </select>

    <button type="submit">
        Tìm kiếm
    </button>

    <a href="{{ route('inventory.index') }}">
        Làm mới
    </a>

</form>

<hr>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Sản phẩm</th>
        <th>Màu</th>
        <th>Dung lượng</th>
        <th>Loại giao dịch</th>
        <th>Số lượng</th>
        <th>Ghi chú</th>
        <th>Thời gian</th>
        <th>Hành động</th>
    </tr>

    @forelse($transactions as $item)

        <tr>

            <td>{{ $item->id }}</td>

            <td>
                {{ $item->productVariant?->product?->name ?? 'N/A' }}
            </td>

            <td>
                {{ $item->productVariant?->color ?? 'N/A' }}
            </td>

            <td>
                {{ $item->productVariant?->storage ?? 'N/A' }}
            </td>

            <td>

                @if($item->type == 'import')

                    Nhập kho

                @elseif($item->type == 'export')

                    Xuất kho

                @elseif($item->type == 'adjustment')

                    Điều chỉnh

                @else

                    Không xác định

                @endif

            </td>

            <td>
                {{ $item->quantity }}
            </td>

            <td>
                {{ $item->note }}
            </td>

            <td>
                {{ $item->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
            </td>

            <td>

                <a href="{{ route('inventory.edit', $item->id) }}">
                    Sửa
                </a>

            </td>

        </tr>

    @empty

        <tr>

            <td colspan="9">
                Không có dữ liệu
            </td>

        </tr>

    @endforelse

</table>

<br>

{{ $transactions->withQueryString()->links() }}