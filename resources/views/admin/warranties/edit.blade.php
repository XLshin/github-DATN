@extends('layouts.admin')

@section('title', 'Hoàn tất xử lý bảo hành')
@section('page_icon', 'bi-check2-circle')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Hoàn tất xử lý bảo hành')
@section('page_subtitle', 'Xác định nguyên nhân lỗi và lựa chọn sửa chữa hoặc đổi máy mới theo chính sách 30 ngày.')

@section('heading_actions')
<a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại chi tiết
</a>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header"><h5 class="mb-1">Thông tin phiếu</h5></div>
            <div class="p-3">
                <div class="mb-3"><div class="text-muted small">Mã phiếu</div><div class="fw-semibold">{{ $warranty->warranty_code }}</div></div>
                <div class="mb-3"><div class="text-muted small">IMEI máy đang bảo hành</div><div class="fw-semibold">{{ $warranty->imei?->imei ?? 'N/A' }}</div></div>
                <div class="mb-3"><div class="text-muted small">Lỗi khách báo</div><div class="border rounded p-2 bg-light text-danger">{{ $warranty->customer_note ?? 'Chưa có ghi chú.' }}</div></div>

                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="fw-bold">Chính sách đổi máy mới trong 30 ngày</h6>
                        <div class="small">Ngày mua/nhận máy: <strong>{{ $purchaseDate->format('d/m/Y') }}</strong></div>
                        <div class="small">Hạn đổi máy: <strong>{{ $replacementDeadline->format('d/m/Y') }}</strong></div>
                        @if($isWithinReplacementPeriod)
                            <div class="alert alert-success mt-3 mb-0 small">Máy còn trong 30 ngày. Nếu lỗi do cửa hàng hoặc do hãng, khách đủ điều kiện đổi máy mới.</div>
                        @else
                            <div class="alert alert-secondary mt-3 mb-0 small">Máy đã quá 30 ngày nên không còn đủ điều kiện đổi máy mới.</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header"><h5 class="mb-1 text-primary">Cập nhật kết quả xử lý</h5></div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.update', $warranty) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="active">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nguyên nhân lỗi <span class="text-danger">*</span></label>
                        <select name="fault_source" id="fault_source" class="form-select @error('fault_source') is-invalid @enderror" required>
                            <option value="">-- Chọn nguyên nhân lỗi --</option>
                            <option value="store" @selected(old('fault_source', $warranty->fault_source) === 'store')>Lỗi do cửa hàng</option>
                            <option value="manufacturer" @selected(old('fault_source', $warranty->fault_source) === 'manufacturer')>Lỗi do hãng</option>
                            <option value="customer" @selected(old('fault_source', $warranty->fault_source) === 'customer')>Lỗi do khách hàng</option>
                            <option value="unknown" @selected(old('fault_source', $warranty->fault_source) === 'unknown')>Chưa xác định</option>
                        </select>
                        @error('fault_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hình thức xử lý <span class="text-danger">*</span></label>
                        <select name="resolution_type" id="resolution_type" class="form-select @error('resolution_type') is-invalid @enderror" required>
                            <option value="">-- Chọn hình thức xử lý --</option>
                            <option value="repair" @selected(old('resolution_type', $warranty->resolution_type) === 'repair')>Sửa chữa bảo hành</option>
                            @if($isWithinReplacementPeriod)
                                <option value="replace" @selected(old('resolution_type', $warranty->resolution_type) === 'replace')>Đổi máy mới</option>
                            @endif
                            <option value="reject" @selected(old('resolution_type', $warranty->resolution_type) === 'reject')>Từ chối bảo hành</option>
                        </select>
                        @error('resolution_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div id="replacement-imei-section" class="mb-3" style="display:none">
                        <label class="form-label fw-semibold">IMEI máy mới <span class="text-danger">*</span></label>
                        <select name="replacement_imei_id" id="replacement_imei_id" class="form-select @error('replacement_imei_id') is-invalid @enderror">
                            <option value="">-- Chọn IMEI máy mới cùng phiên bản --</option>
                            @foreach($replacementImeis as $replacementImei)
                                <option value="{{ $replacementImei->id }}" @selected((string) old('replacement_imei_id', $warranty->replacement_imei_id) === (string) $replacementImei->id)>{{ $replacementImei->imei }}</option>
                            @endforeach
                        </select>
                        @error('replacement_imei_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($replacementImeis->isEmpty())<div class="text-danger small mt-1">Hiện không có máy mới cùng phiên bản trong kho.</div>@endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú cập nhật trạng thái (nội bộ)</label>
                        <textarea name="status_update_note" rows="2" class="form-control @error('status_update_note') is-invalid @enderror" required>{{ old('status_update_note', $warranty->status_update_note) }}</textarea>
                        @error('status_update_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kết quả xử lý / linh kiện hoặc máy thay thế</label>
                        <textarea name="repair_result_note" rows="4" class="form-control @error('repair_result_note') is-invalid @enderror" required>{{ old('repair_result_note', $warranty->repair_result_note) }}</textarea>
                        @error('repair_result_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3"><label class="form-label fw-semibold">Ảnh sau xử lý hoặc ảnh máy mới</label><input type="file" name="completion_images[]" class="form-control" accept="image/*" multiple></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Video sau xử lý</label><input type="file" name="completion_videos[]" class="form-control" accept="video/*" multiple></div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Xác nhận hoàn tất xử lý</button>
                        <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light">Hủy</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resolutionSelect = document.getElementById('resolution_type');
    const replacementSection = document.getElementById('replacement-imei-section');
    const replacementImei = document.getElementById('replacement_imei_id');

    function toggleReplacementImei() {
        const isReplace = resolutionSelect.value === 'replace';
        replacementSection.style.display = isReplace ? 'block' : 'none';
        replacementImei.required = isReplace;
        if (!isReplace) replacementImei.value = '';
    }

    resolutionSelect.addEventListener('change', toggleReplacementImei);
    toggleReplacementImei();
});
</script>
@endpush
