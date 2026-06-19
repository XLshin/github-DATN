@extends('layouts.admin')

@section('title', 'Sửa giao dịch kho')
@section('page_icon', 'bi-pencil-square')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Sửa giao dịch kho')
@section('page_subtitle', 'Cập nhật số lượng và ghi chú cho giao dịch kho.')

@section('heading_actions')
<a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin giao dịch</h5>
            <div class="text-muted small">
                Một số thông tin giao dịch chỉ được xem, không thể chỉnh sửa.
            </div>
        </div>
    </div>

    <div class="p-3">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('inventory.update', $transaction->id) }}" method="POST" style="max-width: 700px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">
                    Variant ID
                </label>

                <input
                    type="text"
                    value="{{ $transaction->product_variant_id }}"
                    class="form-control"
                    readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Loại giao dịch
                </label>

                <input
                    type="text"
                    value="@if($transaction->type === 'import') Nhập kho @elseif($transaction->type === 'export') Xuất kho @elseif($transaction->type === 'adjustment') Điều chỉnh @else {{ $transaction->type }} @endif"
                    class="form-control"
                    readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Số lượng <span class="text-danger">*</span>
                </label>

                <input
                    type="number"
                    name="quantity"
                    value="{{ old('quantity', $transaction->quantity) }}"
                    class="form-control @error('quantity') is-invalid @enderror"
                    placeholder="Nhập số lượng">

                @error('quantity')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Ghi chú
                </label>

                <textarea
                    name="note"
                    rows="4"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Nhập ghi chú nếu có">{{ old('note', $transaction->note) }}</textarea>

                @error('note')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Ngày tạo
                </label>

                <input
                    type="text"
                    value="{{ $transaction->created_at?->format('d/m/Y H:i') }}"
                    class="form-control"
                    readonly>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Cập nhật
                </button>

                <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection