<h1>Sửa IMEI</h1>

<form action="{{ route('imeis.update',$imei->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <div>
        <label>IMEI</label>
        <input type="text"
               name="imei"
               value="{{ $imei->imei }}">
    </div>

    <br>

    <div>
        <label>Trạng thái</label>

        <select name="status">
            <option value="available"
                {{ $imei->status == 'available' ? 'selected' : '' }}>
                Available
            </option>

            <option value="reserved"
                {{ $imei->status == 'reserved' ? 'selected' : '' }}>
                Reserved
            </option>

            <option value="sold"
                {{ $imei->status == 'sold' ? 'selected' : '' }}>
                Sold
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