@extends('layouts.admin')

@section('title', 'Hoàn tất xử lý bảo hành')
@section('page_icon', 'bi-check2-circle')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Hoàn tất xử lý bảo hành')
@section('page_subtitle', 'Nhập kết quả sửa chữa và minh chứng để chuyển trạng thái đơn sang Hoàn tất.')

@section('heading_actions')
<a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại chi tiết
</a>
@endsection

@section('content')

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <h5 class="mb-1">Thông tin phiếu</h5>
            </div>
            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã phiếu</div>
                    <div class="fw-semibold">{{ $warranty->warranty_code }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Lỗi khách báo</div>
                    <div class="border rounded p-2 bg-light text-danger">
                        {{ $warranty->customer_note ?? 'Chưa có ghi chú.' }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <h5 class="mb-1 text-primary">Cập nhật kết quả sửa chữa</h5>
            </div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.update', $warranty) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="status" value="active">

                    <div class="alert alert-warning">
                        Bạn đang xác nhận <strong>Hoàn tất xử lý</strong> cho phiếu bảo hành này.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú cập nhật trạng thái (Nội bộ)</label>
                        <textarea name="status_update_note" rows="2" class="form-control" placeholder="Ghi chú nhanh về việc hoàn tất...">{{ old('status_update_note', $warranty->status_update_note) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kết quả sửa chữa / bộ phận thay thế</label>
                        <textarea name="repair_result_note" rows="4" class="form-control @error('repair_result_note') is-invalid @enderror" required>{{ old('repair_result_note', $warranty->repair_result_note) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ảnh sau khi sửa xong</label>
                        <input type="file" name="completion_images[]" class="form-control" accept="image/*" multiple>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Video sau khi sửa xong</label>
                        <input type="file" name="completion_videos[]" class="form-control" accept="video/*" multiple>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Xác nhận Hoàn tất xử lý
                        </button>
                        <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light">Hủy</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection