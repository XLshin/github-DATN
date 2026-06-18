<h1>Sửa giao dịch kho</h1>

@if ($errors->any())

    <div>

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form action="{{ route('inventory.update', $transaction->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <div>

        <label>Variant ID</label>

        <input
            type="text"
            value="{{ $transaction->product_variant_id }}"
            readonly>

    </div>

    <br>

    <div>

        <label>Loại giao dịch</label>

        <input
            type="text"
            value="{{ $transaction->type }}"
            readonly>

    </div>

    <br>

    <div>

        <label>Số lượng</label>

        <input
            type="number"
            name="quantity"
            value="{{ old('quantity', $transaction->quantity) }}">

    </div>

    <br>

    <div>

        <label>Ghi chú</label>

        <textarea
            name="note">{{ old('note', $transaction->note) }}</textarea>

    </div>

    <br>

    <div>

        <label>Ngày tạo</label>

        <input
            type="text"
            value="{{ $transaction->created_at?->format('d/m/Y H:i') }}"
            readonly>

    </div>

    <br>

    <button type="submit">

        Cập nhật

    </button>

</form>

<br>

<a href="{{ route('inventory.index') }}">
    Quay lại
</a>