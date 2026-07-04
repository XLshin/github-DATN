@extends('layouts.admin')

@section('title', 'Cập nhật trạng thái bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Cập nhật trạng thái bảo hành')
@section('page_subtitle', 'Cập nhật ghi chú xác nhận, kết quả xử lý và ảnh/video chứng minh sau khi sửa xong.')

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
    Vui lòng kiểm tra lại thông tin cập nhật phiếu bảo hành.
</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin phiếu</h5>
                    <div class="text-muted small">
                        Thông tin đơn hàng, sản phẩm và IMEI liên quan.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã phiếu</div>
                    <div class="fw-semibold">
                        {{ $warranty->warranty_code }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">IMEI</div>
                    <div class="fw-semibold">
                        {{ $warranty->imei->imei ?? $warrantyDetail->imei ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Sản phẩm</div>

                    @if($warrantyDetail)
                        <div class="fw-semibold">
                            {{ $warrantyDetail->product_name }}
                        </div>
                        <div class="text-muted small">
                            {{ $warrantyDetail->color }} - {{ $warrantyDetail->storage }}
                        </div>
                    @else
                        <div class="fw-semibold">Không có</div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Mã đơn</div>
                    <div class="fw-semibold">
                        {{ $warranty->order->order_code ?? $warrantyDetail->order_code ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $warranty->order->customer_name ?? $warrantyDetail->customer_name ?? 'Không có' }}
                    </div>
                    <div class="text-muted small">
                        {{ $warranty->order->customer_phone ?? $warrantyDetail->customer_phone ?? '' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Thời hạn bảo hành</div>
                    <div class="fw-semibold">
                        {{ $warranty->warranty_start ? $warranty->warranty_start->format('d/m/Y') : 'N/A' }}
                        -
                        {{ $warranty->warranty_end ? $warranty->warranty_end->format('d/m/Y') : 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Trạng thái hiện tại</div>
                    <span class="badge text-bg-{{ $warranty->status_badge }}">
                        {{ $warranty->status_label }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Trạng thái IMEI</div>
                    <div class="fw-semibold">
                        {{ $warrantyDetail->imei_status ?? $warranty->imei->status ?? 'Không có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small mb-1">Lỗi khách báo / ghi chú tiếp nhận</div>

                    @if($warranty->customer_note)
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($warranty->customer_note)) !!}
                        </div>
                    @else
                        <div class="text-muted">
                            Chưa có ghi chú.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Cập nhật xử lý bảo hành</h5>
                    <div class="text-muted small">
                        Khi chuyển trạng thái phải có ghi chú xác nhận. Nếu hoàn tất thì cần thêm kết quả sửa và ảnh sau sửa.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.update', $warranty) }}" enctype="multipart/form-data" style="max-width: 760px;">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">
                            Trạng thái xử lý <span class="text-danger">*</span>
                        </label>

                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="claimed" @selected(old('status', $warranty->status) === 'claimed')>
                                Đang xử lý bảo hành
                            </option>

                            <option value="active" @selected(in_array(old('status', $warranty->status), ['active', 'expired'], true))>
                                Hoàn tất xử lý
                            </option>
                        </select>

                        @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ghi chú xác nhận khi cập nhật trạng thái <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="status_update_note"
                            rows="4"
                            class="form-control @error('status_update_note') is-invalid @enderror"
                            placeholder="Ví dụ: Đã tiếp nhận máy, kỹ thuật kiểm tra thấy lỗi pin/sạc. Hoặc: Đã hoàn tất sửa chữa và bàn giao lại máy.">{{ old('status_update_note', $warranty->status_update_note) }}</textarea>

                        @error('status_update_note')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Trường này bắt buộc để lưu lại lý do hoặc xác nhận khi chuyển trạng thái.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Kết quả sửa chữa / nội dung đã xử lý
                        </label>

                        <textarea
                            name="repair_result_note"
                            rows="4"
                            class="form-control @error('repair_result_note') is-invalid @enderror"
                            placeholder="Ví dụ: Đã thay pin mới, vệ sinh cổng sạc, test sạc ổn định trong 30 phút.">{{ old('repair_result_note', $warranty->repair_result_note) }}</textarea>

                        @error('repair_result_note')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Bắt buộc nhập khi chuyển trạng thái sang Hoàn tất xử lý.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ảnh sau khi sửa xong
                        </label>

                        <input
                            type="file"
                            name="completion_images[]"
                            class="form-control @error('completion_images') is-invalid @enderror @error('completion_images.*') is-invalid @enderror"
                            accept="image/*"
                            multiple>

                        @error('completion_images')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror

                        @error('completion_images.*')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Khi hoàn tất bảo hành, cần có ít nhất 1 ảnh sau sửa. Mỗi ảnh tối đa 10MB.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Video sau khi sửa xong
                        </label>

                        <input
                            type="file"
                            name="completion_videos[]"
                            class="form-control @error('completion_videos') is-invalid @enderror @error('completion_videos.*') is-invalid @enderror"
                            accept="video/*"
                            multiple>

                        @error('completion_videos')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror

                        @error('completion_videos.*')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            Video không bắt buộc. Mỗi video tối đa 100MB.
                        </div>
                    </div>

                    @if($warranty->completionMedia->count())
                        <div class="mb-3">
                            <div class="text-muted small mb-2">Minh chứng sau sửa đã upload</div>

                            <div class="row g-2">
                                @foreach($warranty->completionMedia as $media)
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100">
                                            @if($media->type === \App\Models\WarrantyMedia::TYPE_IMAGE)
                                                <a href="{{ $media->url }}" target="_blank">
                                                    <img
                                                        src="{{ $media->url }}"
                                                        alt="Minh chứng sau sửa"
                                                        class="img-fluid rounded">
                                                </a>
                                            @else
                                                <video
                                                    src="{{ $media->url }}"
                                                    controls
                                                    class="w-100 rounded">
                                                </video>
                                            @endif

                                            <div class="small text-muted mt-1">
                                                {{ $media->type_label }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <strong>Đang xử lý bảo hành</strong>: IMEI sẽ ở trạng thái <strong>warranty</strong> và chưa được tạo phiếu mới.<br>
                        <strong>Hoàn tất xử lý</strong>: IMEI chuyển về <strong>sold</strong>, sau này vẫn có thể tạo phiếu mới nếu còn thời hạn bảo hành thật sự.<br>
                        Khi hoàn tất, hệ thống lưu thời gian hoàn tất vào cột <strong>completed_at</strong>.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Xác nhận cập nhật
                        </button>

                        <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

@endsection