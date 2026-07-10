@extends('layouts.admin')

@section('title', 'Chi tiết bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Chi tiết phiếu bảo hành')
@section('page_subtitle', 'Xem chi tiết tiến độ, minh chứng tiếp nhận, kết quả xử lý và thông tin bàn giao khách hàng.')

@section('heading_actions')
<a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
</a>

@if($warranty->status === 'claimed')
<a href="{{ route('admin.warranties.edit', $warranty) }}?set_status=active" class="btn btn-primary btn-sm">
    <i class="bi bi-pencil"></i> Cập nhật hoàn tất ngay
</a>
@else
<a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-file-earmark-arrow-up"></i> Cập nhật bổ sung
</a>
<a href="{{ route('admin.warranties.receipt', $warranty) }}" class="btn btn-success btn-sm">
        <i class="bi bi-person-check"></i> Xác nhận bàn giao
    </a>
@endif
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
                    <h5 class="mb-1">Thông tin tiếp nhận ban đầu</h5>
                    <div class="text-muted small">Thông tin khách hàng, sản phẩm và tình trạng máy khi nhận vào.</div>
                </div>
            </div>

            <div class="p-3">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="text-muted small">Mã phiếu bảo hành</div>
                        <div class="fw-bold text-primary fs-5">{{ $warranty->warranty_code }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái hiện tại</div>
                        <div>
                            <span class="badge bg-{{ $warranty->status_badge }} px-2.5 py-1.5 fs-7">
                                {{ $warranty->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Mã đơn hàng liên quan</div>
                        <div class="fw-semibold">{{ $warranty->order->order_code ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Số máy (IMEI)</div>
                        <div class="fw-semibold text-dark">{{ $warranty->imei->imei ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="text-muted small">Tên sản phẩm</div>
                        <div class="fw-semibold">{{ $warranty->imei->productVariant->product->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="text-muted small">Họ tên khách hàng</div>
                        <div class="fw-semibold">{{ $warranty->order->customer_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Số điện thoại liên hệ</div>
                        <div class="fw-semibold">{{ $warranty->order->customer_phone ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="text-muted small mb-1">Mô tả lỗi từ khách hàng / Ghi chú tiếp nhận</div>
                    <div class="p-3 border rounded bg-light text-danger fw-medium">
                        {{ $warranty->customer_note ?? 'Không có ghi chú lỗi.' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small mb-2 fw-semibold">Hình ảnh/Video minh chứng lúc nhận máy:</div>
                    @if($warranty->receptionMedia && $warranty->receptionMedia->count())
                        <div class="row g-2">
                            @foreach($warranty->receptionMedia as $media)
                                <div class="col-md-4 col-sm-6">
                                    <div class="border rounded p-2 text-center bg-white h-100 shadow-xs">
                                        @if($media->type === 'image')
                                            <a href="{{ $media->url }}" target="_blank">
                                                <img src="{{ $media->url }}" class="img-fluid rounded shadow-sm" style="max-height: 120px; object-fit: cover;">
                                            </a>
                                        @else
                                            <video src="{{ $media->url }}" controls class="img-fluid rounded shadow-sm" style="max-height: 120px; width: 100%; object-fit: cover;"></video>
                                        @endif
                                        <div class="small text-muted mt-1 text-truncate" title="{{ $media->original_name }}">
                                            {{ $media->original_name }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small italic p-2 border border-dashed rounded bg-light text-center">
                            Không có ảnh hoặc video minh chứng lúc tiếp nhận.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Kết quả và Bàn giao</h5>
                    <div class="text-muted small">Chi tiết quá trình khắc phục và bàn giao thiết bị.</div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-4">
                    <div class="text-muted small">Ghi chú cập nhật trạng thái nội bộ</div>
                    <div class="fw-medium text-dark p-2 bg-light border rounded">
                        {{ $warranty->status_update_note ?? '(Trống)' }}
                    </div>
                </div>

                <div class="card border-primary-subtle bg-light-subtle mb-4 shadow-sm">
                    <div class="card-body p-3">
                        <h6 class="text-primary fw-bold mb-3">
                            <i class="bi bi-tools"></i> 1. Kết quả kỹ thuật xử lý
                        </h6>
                        
                        <div class="mb-3">
                            <div class="text-muted small">Nội dung / Linh kiện thay thế:</div>
                            <div class="fw-semibold text-dark ps-2 border-start border-2 border-primary py-1 mt-1">
                                {!! nl2br(e($warranty->repair_result_note ?? 'Chưa cập nhật nội dung xử lý sửa chữa.')) !!}
                            </div>
                        </div>

                        <div>
                            <div class="text-muted small mb-2">Ảnh/Video chứng minh sản phẩm đã sửa xong:</div>
                            @if($warranty->completionMedia && $warranty->completionMedia->count())
                                <div class="row g-2">
                                    @foreach($warranty->completionMedia as $media)
                                        <div class="col-6">
                                            <div class="border rounded p-1.5 bg-white text-center h-100">
                                                @if($media->type === 'image')
                                                    <a href="{{ $media->url }}" target="_blank">
                                                        <img src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                                    </a>
                                                @else
                                                    <video src="{{ $media->url }}" controls class="img-fluid rounded" style="max-height: 80px; width: 100%; object-fit: cover;"></video>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small text-center py-2 bg-white border border-dashed rounded">
                                    Chưa có hình ảnh/video kết quả sửa chữa.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card border-success-subtle bg-light-subtle shadow-sm">
                    <div class="card-body p-3">
                        <h6 class="text-success fw-bold mb-3">
                            <i class="bi bi-person-check"></i> 2. Xác nhận bàn giao khách hàng
                        </h6>

                        <div class="mb-3">
                            <div class="text-muted small">Ghi chú lúc trả máy:</div>
                            <div class="fw-semibold text-dark ps-2 border-start border-2 border-success py-1 mt-1">
                                {!! nl2br(e($warranty->customer_receipt_note ?? 'Chưa cập nhật ghi chú ký nhận/bàn giao thiết bị.')) !!}
                            </div>
                        </div>

                        <div>
                            <div class="text-muted small mb-2">Ảnh chụp biên bản / Phiếu xuất ký nhận:</div>
                            @if($warranty->receiptMedia && $warranty->receiptMedia->count())
                                <div class="row g-2">
                                    @foreach($warranty->receiptMedia as $media)
                                        <div class="col-6">
                                            <div class="border rounded p-1.5 bg-white text-center h-100">
                                                <a href="{{ $media->url }}" target="_blank">
                                                    <img src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small text-center py-2 bg-white border border-dashed rounded">
                                    Chưa có minh chứng bàn giao sản phẩm.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Lịch sử sự kiện bảo hành</h5>
            <div class="text-muted small">Các mốc thời gian và hành động ghi nhận thay đổi trên hệ thống.</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width: 200px;">Thời gian</th>
                    <th style="width: 250px;">Sự kiện</th>
                    <th>Nội dung chi tiết diễn giải</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($histories as $history)
                <tr>
                    <td class="fw-semibold text-secondary">
                        @if($history['time'] instanceof \Carbon\CarbonInterface)
                            {{ $history['time']->format('d/m/Y H:i') }}
                        @else
                            {{ $history['time'] }}
                        @endif
                    </td>

                    <td class="fw-bold text-dark">
                        {{ $history['title'] }}
                    </td>

                    <td class="text-muted">
                        {{ $history['description'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        Chưa ghi nhận tiến trình lịch sử bảo hành nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection