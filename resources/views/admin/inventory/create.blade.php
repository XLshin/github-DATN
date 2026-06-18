<h1>Nhập kho</h1>

@if ($errors->any())

    <div>

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form action="{{ route('inventory.store') }}"
      method="POST">

    @csrf

    <div>

        <label>Product Variant ID</label>

        <input
            type="number"
            name="product_variant_id"
            value="{{ old('product_variant_id', 1) }}">

    </div>

    <br>

    <div>

        <label>Loại giao dịch</label>

        <input
            type="text"
            value="Nhập kho"
            readonly>

    </div>

    <br>

    <div>

        <label>Số lượng</label>

        <input
            type="number"
            name="quantity"
            value="{{ old('quantity') }}">

    </div>

    <br>

    <div>

        <label>Ghi chú</label>

        <textarea
            name="note">{{ old('note') }}</textarea>

    </div>

    <br>

    <button type="submit">

        Lưu

    </button>

</form>

<br>

<a href="{{ route('inventory.index') }}">
    Quay lại
</a>