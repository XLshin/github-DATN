@extends('layouts.client')

@section('title', 'Chi tiết phiếu bảo hành')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">Chi tiết phiếu bảo hành</h1>

    @php
        $isWarrantyExpired = $warranty->warranty_end
            ? now()->startOfDay()->gt($warranty->warranty_end->copy()->startOfDay())
            : false;
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Mã phiếu bảo hành</div>
                            <div class="fw-bold text-primary fs-5">{{ $warranty->warranty_code }}</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small">Trạng thái hiện tại</div>
                            <div>
                                <span class="badge bg-{{ $warranty->status_badge }} px-3 py-2 fs-7">
                                    {{ $warranty->status_label }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Mã đơn hàng liên quan</div>
                            <div class="fw-semibold">{{ $warranty->order?->order_code ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Số máy (IMEI)</div>
                            <div class="fw-semibold text-dark">{{ $warranty->imei?->imei ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Tên sản phẩm</div>
                            <div class="fw-semibold">{{ $warranty->imei?->productVariant?->product?->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Họ tên khách hàng</div>
                            <div class="fw-semibold">{{ $warranty->order?->customer_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Số điện thoại liên hệ</div>
                            <div class="fw-semibold">{{ $warranty->order?->customer_phone ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Thời hạn bảo hành</div>
                            <div class="fw-semibold">{{ $warranty->warranty_start?->format('d/m/Y') ?? '-' }} - {{ $warranty->warranty_end?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small">Tình trạng bảo hành</div>
                            <div>
                                <span class="badge bg-{{ $isWarrantyExpired ? 'danger' : 'success' }}">
                                    {{ $isWarrantyExpired ? 'Đã quá hạn' : 'Còn hạn' }}
                                </span>
                            </div>
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
                        @if($warranty->receptionMedia->count())
                            <div class="row g-2">
                                @foreach($warranty->receptionMedia as $media)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="border rounded p-2 text-center bg-white h-100 shadow-sm">
                                            @if($media->type === 'image')
                                                <a href="{{ $media->url }}" target="_blank">
                                                    <img src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 120px; object-fit: cover;">
                                                </a>
                                            @else
                                                <video src="{{ $media->url }}" controls class="img-fluid rounded" style="max-height: 120px; width: 100%; object-fit: cover;"></video>
                                            @endif
                                            <div class="small text-muted mt-1 text-truncate" title="{{ $media->original_name }}">
                                                {{ $media->original_name }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small text-center py-3 border border-dashed rounded bg-light">
                                Không có ảnh hoặc video minh chứng lúc tiếp nhận.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="text-muted small">Ghi chú cập nhật trạng thái nội bộ</div>
                        <div class="fw-medium text-dark p-3 bg-light border rounded">
                            {{ $warranty->status_update_note ?? 'Chưa có ghi chú cập nhật.' }}
                        </div>
                    </div>

                    <div class="card border-primary-subtle bg-light-subtle mb-4 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="text-primary fw-bold mb-3">
                                <i class="bi bi-tools"></i> Kết quả kỹ thuật xử lý
                            </h6>

                            <div class="mb-3">
                                <div class="text-muted small">Nội dung / Linh kiện thay thế</div>
                                <div class="fw-semibold text-dark ps-2 border-start border-3 border-primary py-2 mt-2">
                                    {!! nl2br(e($warranty->repair_result_note ?? 'Chưa cập nhật nội dung xử lý sửa chữa.')) !!}
                                </div>
                            </div>

                            <div>
                                <div class="text-muted small mb-2">Ảnh/Video chứng minh sản phẩm đã sửa xong</div>
                                @if($warranty->completionMedia->count())
                                    <div class="row g-2">
                                        @foreach($warranty->completionMedia as $media)
                                            <div class="col-6">
                                                <div class="border rounded p-1 bg-white text-center h-100">
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
                                <i class="bi bi-person-check"></i> Xác nhận bàn giao khách hàng
                            </h6>

                            <div class="mb-3">
                                <div class="text-muted small">Ghi chú lúc trả máy</div>
                                <div class="fw-semibold text-dark ps-2 border-start border-3 border-success py-2 mt-2">
                                    {!! nl2br(e($warranty->customer_receipt_note ?? 'Chưa cập nhật ghi chú ký nhận/bàn giao thiết bị.')) !!}
                                </div>
                            </div>

                            <div>
                                <div class="text-muted small mb-2">Ảnh chụp biên bản / Phiếu xuất ký nhận</div>
                                @if($warranty->receiptMedia->count())
                                    <div class="row g-2">
                                        @foreach($warranty->receiptMedia as $media)
                                            <div class="col-6">
                                                <div class="border rounded p-1 bg-white text-center h-100">
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
                                        Chưa có minh chứng bàn giao sản phẩm.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Lịch sử sự kiện bảo hành</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 180px;">Thời gian</th>
                            <th style="width: 240px;">Sự kiện</th>
                            <th>Nội dung chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $history)
                            <tr>
                                <td class="fw-semibold text-secondary">
                                    @if($history['time'] instanceof \Carbon\CarbonInterface)
                                        {{ $history['time']->format('d/m/Y H:i') }}
                                    @else
                                        {{ $history['time'] }}
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">{{ $history['title'] }}</td>
                                <td class="text-muted">{{ $history['description'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Chưa có lịch sử nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="{{ route('warranties.lookup') }}" class="btn btn-light">← Quay lại tra cứu</a>
</div>
@endsection
