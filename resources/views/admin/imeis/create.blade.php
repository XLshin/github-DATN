<h1>Thêm IMEI</h1>

<form action="{{ route('imeis.store') }}"
      method="POST">

    @csrf

    <div>
        <label>IMEI</label>
        <input type="text"
               name="imei">
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