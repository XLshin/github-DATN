@extends('layouts.admin')

@section('title', 'Chi tiết bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Chi tiết bảo hành')
@section('page_subtitle', 'Xem thông tin phiếu bảo hành, lỗi khách báo, minh chứng tiếp nhận, kết quả sau sửa và lịch sử.')

@section('heading_actions')
<a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
</a>

<a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-pencil"></i> Cập nhật trạng thái
</a>
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin phiếu</h5>
                    <div class="text-muted small">
                        Thông tin IMEI, sản phẩm, đơn hàng, thời hạn bảo hành và lỗi khách báo.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Mã phiếu</div>
                        <div class="fw-semibold">
                            {{ $warranty->warranty_code }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">IMEI</div>
                        <div class="fw-semibold">
                            {{ $warranty->imei->imei ?? $warrantyDetail->imei ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
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

                    <div class="col-md-6">
                        <div class="text-muted small">Mã đơn</div>
                        <div class="fw-semibold">
                            {{ $warranty->order->order_code ?? $warrantyDetail->order_code ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Khách hàng</div>
                        <div class="fw-semibold">
                            {{ $warranty->order->customer_name ?? $warrantyDetail->customer_name ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">SĐT</div>
                        <div class="fw-semibold">
                            {{ $warranty->order->customer_phone ?? $warrantyDetail->customer_phone ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Ngày bắt đầu bảo hành</div>
                        <div class="fw-semibold">
                            {{ $warranty->warranty_start ? $warranty->warranty_start->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Ngày hết hạn bảo hành</div>
                        <div class="fw-semibold">
                            {{ $warranty->warranty_end ? $warranty->warranty_end->format('d/m/Y') : 'N/A' }}
                        </div>

                        @if($warranty->warranty_end && now()->startOfDay()->gt($warranty->warranty_end->copy()->startOfDay()))
                            <div class="text-danger small">
                                IMEI đã quá hạn bảo hành
                            </div>
                        @elseif($warranty->warranty_end)
                            <div class="text-success small">
                                IMEI còn hạn bảo hành
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái phiếu</div>
                        <span class="badge text-bg-{{ $warranty->status_badge }}">
                            {{ $warranty->status_label }}
                        </span>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái IMEI</div>
                        <div class="fw-semibold">
                            {{ $warrantyDetail->imei_status ?? $warranty->imei->status ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-muted small mb-1">
                            Lỗi khách báo / ghi chú tiếp nhận
                        </div>

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
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin xử lý</h5>
                    <div class="text-muted small">
                        Ghi chú xác nhận và kết quả sau khi sửa xong.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small mb-1">
                        Ghi chú cập nhật trạng thái
                    </div>

                    @if($warranty->status_update_note)
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($warranty->status_update_note)) !!}
                        </div>
                    @else
                        <div class="text-muted">
                            Chưa có ghi chú cập nhật.
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small mb-1">
                        Kết quả sửa chữa / bảo hành
                    </div>

                    @if($warranty->repair_result_note)
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($warranty->repair_result_note)) !!}
                        </div>
                    @else
                        <div class="text-muted">
                            Chưa có kết quả sửa chữa.
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">
                        Thời gian hoàn tất
                    </div>

                    <div class="fw-semibold">
                        {{ $warranty->completed_at ? $warranty->completed_at->format('d/m/Y H:i') : 'Chưa hoàn tất' }}
                    </div>
                </div>

                <a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil-square"></i> Cập nhật trạng thái bảo hành
                </a>

                <div class="alert alert-info mt-3 mb-0">
                    Trang chi tiết chỉ dùng để xem thông tin. Muốn cập nhật trạng thái, ghi chú hoặc minh chứng sau sửa thì bấm nút cập nhật.
                </div>
            </div>
        </section>
    </div>
</div>

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Minh chứng tình trạng máy lúc tiếp nhận</h5>
            <div class="text-muted small">
                Ảnh/video được upload khi tạo phiếu bảo hành.
            </div>
        </div>
    </div>

    <div class="p-3">
        @if($warranty->receptionMedia->count())
            <div class="row g-3">
                @foreach($warranty->receptionMedia as $media)
                    <div class="col-md-4">
                        @php
                            $mediaUrl = asset('storage/' . $media->file_path);
                        @endphp

                        <div class="border rounded p-2 h-100 d-flex flex-column">

                            <div class="media-box mb-2">
                                @if($media->type === \App\Models\WarrantyMedia::TYPE_IMAGE)
                                    <a href="{{ $mediaUrl }}" target="_blank">
                                        <img src="{{ $mediaUrl }}" class="media-content">
                                    </a>
                                @else
                                    <video controls class="media-content">
                                        <source src="{{ $mediaUrl }}" type="{{ $media->mime_type }}">
                                    </video>
                                @endif
                            </div>

                            <div class="small text-muted mt-auto">
                                {{ $media->type_label }}
                                @if($media->created_at)
                                    - {{ $media->created_at->format('d/m/Y H:i') }}
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted">
                Chưa có ảnh/video tiếp nhận.
            </div>
        @endif
    </div>
</section>

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Minh chứng sau khi sửa xong</h5>
            <div class="text-muted small">
                Ảnh bắt buộc và video tùy chọn được upload khi hoàn tất bảo hành.
            </div>
        </div>
    </div>

    <div class="p-3">
        @if($warranty->completionMedia->count())
            <div class="row g-3">
                @foreach($warranty->completionMedia as $media)
                    <div class="col-md-4">
                        @php
                            $mediaUrl = asset('storage/' . $media->file_path);
                        @endphp

                        <div class="border rounded p-2 h-100 d-flex flex-column">

                            <div class="media-box mb-2">
                                @if($media->type === \App\Models\WarrantyMedia::TYPE_IMAGE)
                                    <a href="{{ $mediaUrl }}" target="_blank">
                                        <img src="{{ $mediaUrl }}" class="media-content">
                                    </a>
                                @else
                                    <video controls class="media-content">
                                        <source src="{{ $mediaUrl }}" type="{{ $media->mime_type }}">
                                    </video>
                                @endif
                            </div>

                            <div class="small text-muted mt-auto">
                                {{ $media->type_label }}
                                @if($media->created_at)
                                    - {{ $media->created_at->format('d/m/Y H:i') }}
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted">
                Chưa có ảnh/video sau sửa.
            </div>
        @endif
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Lịch sử bảo hành</h5>
            <div class="text-muted small">
                Các mốc thời gian và sự kiện liên quan đến phiếu bảo hành.
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Sự kiện</th>
                    <th>Mô tả</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($histories as $history)
                <tr>
                    <td class="fw-semibold">
                        @if($history['time'] instanceof \Carbon\CarbonInterface)
                            {{ $history['time']->format('d/m/Y H:i') }}
                        @else
                            {{ $history['time'] }}
                        @endif
                    </td>

                    <td>
                        {{ $history['title'] }}
                    </td>

                    <td>
                        {{ $history['description'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        Chưa có lịch sử bảo hành.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection