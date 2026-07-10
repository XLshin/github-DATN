@extends('layouts.admin')

@section('title', 'Xác nhận bàn giao khách hàng')
@section('page_icon', 'bi-person-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Xác nhận bàn giao máy')
@section('page_subtitle', 'Cập nhật ghi chú và minh chứng khách hàng đã nhận lại thiết bị bảo hành.')

@section('heading_actions')
<a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại chi tiết
</a>
@endsection

@section('content')

@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    Vui lòng kiểm tra lại thông tin.
</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin phiếu</h5>
                </div>
            </div>
            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã phiếu</div>
                    <div class="fw-semibold">{{ $warranty->warranty_code }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">{{ $warranty->order->customer_name ?? 'N/A' }} ({{ $warranty->order->customer_phone ?? 'N/A' }})</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Sản phẩm / IMEI</div>
                    <div class="fw-semibold">{{ $warranty->imei->product->name ?? 'N/A' }} - {{ $warranty->imei->imei ?? 'N/A' }}</div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1 text-success">Cập nhật thông tin bàn giao</h5>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.updateReceipt', $warranty) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú khách nhận lại máy</label>
                        <textarea name="customer_receipt_note" rows="4" class="form-control @error('customer_receipt_note') is-invalid @enderror" placeholder="Ví dụ: Khách đã nhận lại sản phẩm, kiểm tra các chức năng cơ bản hoạt động tốt...">{{ old('customer_receipt_note', $warranty->customer_receipt_note) }}</textarea>
                        @error('customer_receipt_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ảnh minh chứng khách đã nhận (Phiếu xuất / Chữ ký...)</label>
                        <input type="file" name="receipt_images[]" class="form-control @error('receipt_images.*') is-invalid @enderror" accept="image/*" multiple>
                        @error('receipt_images.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($warranty->receiptMedia && $warranty->receiptMedia->count())
                        <div class="mb-4">
                            <div class="text-muted small mb-2">Ảnh minh chứng bàn giao đã upload trước đó:</div>
                            <div class="row g-2">
                                @foreach($warranty->receiptMedia as $media)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="border rounded p-2 text-center h-100 bg-light">
                                            <a href="{{ $media->url }}" target="_blank">
                                                <img src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 90px; object-fit: cover;">
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-save"></i> Lưu thông tin bàn giao
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection