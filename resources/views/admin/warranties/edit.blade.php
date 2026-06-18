@extends('admin.layouts.app')

@section('content')
    <h1>Sửa phiếu bảo hành</h1>

    <form method="POST" action="{{ route('admin.warranties.update', $warranty) }}">
        @csrf
        @method('PUT')

        <div>
            <label>Ngày bắt đầu</label>
            <input type="date" name="warranty_start"
                   value="{{ old('warranty_start', $warranty->warranty_start->format('Y-m-d')) }}">
        </div>

        <div>
            <label>Ngày kết thúc</label>
            <input type="date" name="warranty_end"
                   value="{{ old('warranty_end', $warranty->warranty_end->format('Y-m-d')) }}">
        </div>

        <div>
            <label>Trạng thái</label>
            <select name="status">
                <option value="active" @selected($warranty->status === 'active')>Còn bảo hành</option>
                <option value="expired" @selected($warranty->status === 'expired')>Hết hạn</option>
                <option value="claimed" @selected($warranty->status === 'claimed')>Đang bảo hành</option>
            </select>
        </div>

        <button type="submit">Lưu</button>
    </form>
@endsection