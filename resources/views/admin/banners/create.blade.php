@extends('layouts.admin')

@section('title', 'Thêm Banner')
@section('page_icon', 'bi-image')
@section('page_eyebrow', 'Giao diện')
@section('page_title', 'Thêm Banner')

@section('content')
<section class="panel">
    <div class="panel-header">
        <h2 class="h5 mb-0 section-title"><i class="bi bi-image"></i><span>Thêm banner mới</span></h2>
    </div>
    <div class="p-4">
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Drop zone --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Chọn ảnh <span class="text-danger">*</span></label>
                <div id="dropZone"
                     class="border border-2 border-dashed rounded-3 p-4 text-center text-muted"
                     style="cursor:pointer;transition:.2s"
                     onclick="document.getElementById('fileInput').click()"
                     ondragover="event.preventDefault();this.classList.add('border-primary')"
                     ondragleave="this.classList.remove('border-primary')"
                     ondrop="handleDrop(event)">
                    <i class="bi bi-cloud-upload fs-2 d-block mb-1"></i>
                    Kéo thả ảnh vào đây hoặc <strong>click để chọn</strong>
                    <br><small class="text-muted">Nhiều ảnh cùng lúc · JPG, PNG, WEBP · Tối đa 4MB/ảnh</small>
                </div>
                <input type="file" id="fileInput" name="images[]" multiple accept="image/*"
                       class="d-none" onchange="handleFiles(this.files)">
                @error('images') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Preview + tiêu đề từng ảnh --}}
            <div id="previewList" class="mb-3"></div>

            {{-- Link chung --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Link (tùy chọn)</label>
                <input type="url" name="link" class="form-control" value="{{ old('link') }}" placeholder="https://...">
                <small class="text-muted">Áp dụng cho tất cả banner trong lần upload này</small>
            </div>

            {{-- Hẹn giờ --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Hẹn giờ (tùy chọn)</label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Bắt đầu hiện</label>
                        <input type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror"
                               value="{{ old('starts_at') }}">
                        @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Kết thúc hiện</label>
                        <input type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror"
                               value="{{ old('ends_at') }}">
                        @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <small class="text-muted">Để trống nếu không muốn hẹn giờ — banner sẽ tự bật/tắt đúng giờ đã chọn</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="bi bi-cloud-upload"></i> Upload banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Hủy</a>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
let files = [];

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').classList.remove('border-primary');
    handleFiles(e.dataTransfer.files);
}

function handleFiles(newFiles) {
    files = [...files, ...Array.from(newFiles)];
    renderPreviews();
}

function removeFile(index) {
    files.splice(index, 1);
    renderPreviews();
}

function renderPreviews() {
    const list  = document.getElementById('previewList');
    const btn   = document.getElementById('submitBtn');
    const input = document.getElementById('fileInput');

    const dt = new DataTransfer();
    files.forEach(f => dt.items.add(f));
    input.files = dt.files;
    btn.disabled = files.length === 0;

    if (files.length === 0) { list.innerHTML = ''; return; }

    list.innerHTML = `
        <p class="fw-semibold mb-2">Nhập tiêu đề cho từng banner:</p>
        <div class="row g-3">
            ${files.map((f, i) => `
            <div class="col-12 col-md-6">
                <div class="border rounded-3 overflow-hidden bg-white d-flex gap-3 align-items-stretch">
                    <img src="${URL.createObjectURL(f)}"
                         style="width:110px;min-height:80px;object-fit:cover;flex-shrink:0" alt="">
                    <div class="p-2 flex-grow-1 d-flex flex-column justify-content-between">
                        <div>
                            <label class="form-label small text-muted mb-1">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" name="titles[]"
                                   class="form-control form-control-sm"
                                   placeholder="Nhập tiêu đề banner"
                                   value="${f.name.replace(/\.[^.]+$/, '')}">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">${(f.size/1024).toFixed(0)} KB</small>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                    onclick="removeFile(${i})">
                                <i class="bi bi-x"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `).join('')}
        </div>
    `;
}
</script>
@endpush
@endsection
