<h1>Danh sách IMEI</h1>

<a href="{{ route('imeis.create') }}">
    Thêm IMEI
</a>

<hr>

<form method="GET">

    <input
        type="text"
        name="keyword"
        value="{{ request('keyword') }}"
        placeholder="Nhập IMEI cần tìm">

    <select name="status">
        <option value="">Tất cả trạng thái</option>

        <option value="available"
            {{ request('status') == 'available' ? 'selected' : '' }}>
            Available
        </option>

        <option value="sold"
            {{ request('status') == 'sold' ? 'selected' : '' }}>
            Sold
        </option>
    </select>

    <button type="submit">
        Tìm kiếm
    </button>

    <a href="{{ route('imeis.index') }}">
        Làm mới
    </a>

</form>

<hr>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>IMEI</th>
        <th>Trạng thái</th>
        <th>Thao tác</th>
    </tr>

    @forelse($imeis as $imei)

        <tr>
            <td>{{ $imei->id }}</td>

            <td>{{ $imei->imei }}</td>

            <td>{{ $imei->status }}</td>

            <td>

                <a href="{{ route('imeis.edit', $imei->id) }}">
                    Sửa
                </a>

                <form
                    action="{{ route('imeis.destroy', $imei->id) }}"
                    method="POST"
                    style="display:inline">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        onclick="return confirm('Bạn có chắc muốn xóa IMEI này?')">

                        Xóa

                    </button>

                </form>

            </td>
        </tr>

    @empty

        <tr>
            <td colspan="4">
                Không có dữ liệu
            </td>
        </tr>

    @endforelse

</table>

<br>

{{ $imeis->withQueryString()->links() }}