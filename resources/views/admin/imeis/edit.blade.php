<h1>Sửa IMEI</h1>

@if ($errors->any())

    <div>

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form action="{{ route('imeis.update', $imei->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <div>

        <label>Product Variant ID</label>

        <input
            type="number"
            name="product_variant_id"
            value="{{ old('product_variant_id', $imei->product_variant_id) }}">

    </div>

    <br>

    <div>

        <label>IMEI</label>

        <input
            type="text"
            name="imei"
            value="{{ old('imei', $imei->imei) }}">

    </div>

    <br>

    <div>

        <label>Trạng thái</label>

        <select name="status">

            <option
                value="available"
                {{ $imei->status == 'available' ? 'selected' : '' }}>

                Còn hàng

            </option>

            <option
                value="sold"
                {{ $imei->status == 'sold' ? 'selected' : '' }}>

                Đã bán

            </option>

            <option
                value="warranty"
                {{ $imei->status == 'warranty' ? 'selected' : '' }}>

                Bảo hành

            </option>

        </select>

    </div>

    <br>

    <button type="submit">

        Cập nhật

    </button>

    <a href="{{ route('imeis.index') }}">

        Quay lại

    </a>

</form>