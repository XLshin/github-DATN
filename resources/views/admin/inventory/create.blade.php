<h1>Nhập kho</h1>

<form action="{{ route('inventory.store') }}" method="POST">
    @csrf

    <div>
        <label>Product Variant ID</label>
        <input type="number"
               name="product_variant_id"
               value="1">
    </div>

    <br>

    <div>
        <label>Số lượng</label>
        <input type="number"
               name="quantity">
    </div>

    <br>

    <div>
        <label>Ghi chú</label>
        <textarea name="note"></textarea>
    </div>

    <br>

    <button type="submit">
        Lưu
    </button>

</form>

<a href="{{ route('inventory.index') }}">
    Quay lại
</a>