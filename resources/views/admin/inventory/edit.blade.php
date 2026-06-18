<h1>Sửa giao dịch kho</h1>

<form action="{{ route('inventory.update',$transaction->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <input type="number"
           name="quantity"
           value="{{ $transaction->quantity }}">

    <br><br>

    <textarea name="note">
{{ $transaction->note }}
    </textarea>

    <br><br>

    <button type="submit">
        Cập nhật
    </button>
<a href="{{ route('inventory.index') }}">
    Quay lại
</form>