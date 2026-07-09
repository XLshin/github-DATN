@extends('layouts.admin')

@section('title', 'Sửa Banner')
@section('page_icon', 'bi-image')
@section('page_eyebrow', 'Giao diện')
@section('page_title', 'Sửa Banner')

@section('content')
<section class="panel" style="max-width:640px">
    <div class="panel-header">
        <h2 class="h5 mb-0 section-title"><i class="bi bi-image"></i><span>Sửa banner</span></h2>
    </div>
    <div class="p-4">
        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $banner->title) }}">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Ảnh banner</label>
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $banner->image) }}" id="preview"
                         class="rounded" style="max-height:160px;object-fit:cover">
                </div>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                       accept="image/*" onchange="previewImage(this)">
                <small class="text-muted">Để trống nếu không muốn thay ảnh</small>
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Link (tùy chọn)</label>
                <input type="url" name="link" class="form-control @error('link') is-invalid @enderror"
                       value="{{ old('link', $banner->link) }}" placeholder="https://...">
                @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Hẹn giờ --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Hẹn giờ (tùy chọn)</label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Bắt đầu hiện</label>
                        <input type="datetime-local" name="starts_at"
                               class="form-control @error('starts_at') is-invalid @enderror"
                               value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i')) }}">
                        @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Kết thúc hiện</label>
                        <input type="datetime-local" name="ends_at"
                               class="form-control @error('ends_at') is-invalid @enderror"
                               value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i')) }}">
                        @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <small class="text-muted">Để trống để xóa lịch hẹn giờ — banner sẽ tự bật/tắt đúng giờ đã chọn</small>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
                           {{ old('status', $banner->status) ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">Hiển thị ngay (bỏ qua lịch hẹn)</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Hủy</a>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        preview.src = URL.createObjectURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
