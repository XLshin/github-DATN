<h1>Thêm IMEI</h1>

@if ($errors->any())

    <div>

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form action="{{ route('imeis.store') }}"
      method="POST">

    @csrf

    <div>

        <label>Product Variant ID</label>

        <input
            type="number"
            name="product_variant_id"
            value="{{ old('product_variant_id') }}">

    </div>

    <br>

    <div>

        <label>IMEI</label>

        <input
            type="text"
            name="imei"
            value="{{ old('imei') }}">

    </div>

    <br>

    <button type="submit">

        Lưu

    </button>

</form>

<br>

<a href="{{ route('imeis.index') }}">

    Quay lại

</a>