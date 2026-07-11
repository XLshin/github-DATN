@extends('layouts.admin')

@section('title', 'Sửa ghi chú lịch sử kho')
@section('page_icon', 'bi-pencil-square')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Sửa ghi chú lịch sử kho')
@section('page_subtitle', 'Chỉ cho sửa ghi chú. Số lượng tồn kho phải điều chỉnh bằng phiếu điều chỉnh mới.')

@section('heading_actions')
<a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
@php
    $typeLabels = [
        'import' => 'Nhập kho',
        'export' => 'Xuất kho',
        'return' => 'Trả kho',
        'adjustment' => 'Điều chỉnh',
    ];
@endphp

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông tin giao dịch</h5>
            <div class="text-muted small">
                Giao dịch kho là dữ liệu lịch sử. Nếu nhập nhầm số lượng, hãy tạo phiếu điều chỉnh mới.
            </div>
        </div>
    </div>

    <div class="p-3">
        <div class="alert alert-info">
            <div class="fw-semibold">Không sửa số lượng lịch sử</div>
            <div>Sửa số lượng phiếu cũ sẽ làm khó kiểm toán kho. Trang này chỉ dùng để bổ sung hoặc sửa ghi chú.</div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.inventory.update', $transaction->id) }}" method="POST" style="max-width: 760px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Sản phẩm</label>
                <input
                    type="text"
                    value="{{ $transaction->productVariant?->product?->name ?? 'N/A' }}"
                    class="form-control"
                    readonly>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Biến thể</label>
                    <input
                        type="text"
                        value="{{ $transaction->productVariant?->color ?? 'N/A' }}"
                        class="form-control"
                        readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Loại giao dịch</label>
                    <input
                        type="text"
                        value="{{ $typeLabels[$transaction->type] ?? $transaction->type }}"
                        class="form-control"
                        readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Số lượng</label>
                    <input
                        type="text"
                        value="{{ $transaction->quantity }}"
                        class="form-control"
                        readonly>
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label class="form-label">Ghi chú</label>
                <textarea
                    name="note"
                    rows="4"
                    class="form-control @error('note') is-invalid @enderror"
                    placeholder="Nhập ghi chú nếu có">{{ old('note', $transaction->note) }}</textarea>

                @error('note')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Ngày tạo</label>
                <input
                    type="text"
                    value="{{ $transaction->created_at?->format('d/m/Y H:i') }}"
                    class="form-control"
                    readonly>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-lg"></i> Cập nhật ghi chú
                </button>

                <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</section>
@endsection
