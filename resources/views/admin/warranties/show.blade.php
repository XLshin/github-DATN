@extends('layouts.admin')

@section('title', 'Chi tiết bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Chi tiết bảo hành')
@section('page_subtitle', 'Xem thông tin phiếu bảo hành, lỗi khách báo, cập nhật trạng thái và theo dõi lịch sử.')

@section('heading_actions')
<a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
</a>

<a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-pencil"></i> Sửa phiếu
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
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Cập nhật trạng thái</h5>
                    <div class="text-muted small">
                        Chỉ cập nhật trạng thái xử lý phiếu bảo hành.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.updateStatus', $warranty) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">
                            Trạng thái xử lý
                        </label>

                        <select name="status" class="form-select form-select-sm">
                            <option value="claimed" @selected($warranty->status === 'claimed')>
                                Đang xử lý bảo hành
                            </option>

                            <option value="active" @selected(in_array($warranty->status, ['active', 'expired'], true))>
                                Hoàn tất xử lý
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg"></i> Cập nhật
                    </button>
                </form>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Đang xử lý bảo hành</strong>: IMEI sẽ ở trạng thái <strong>warranty</strong> và chưa được tạo phiếu mới.<br>
                    <strong>Hoàn tất xử lý</strong>: IMEI chuyển về <strong>sold</strong>, sau này vẫn có thể tạo phiếu mới nếu còn thời hạn bảo hành thật sự.<br>
                    <strong>Hết hạn bảo hành</strong>: hệ thống tự kiểm tra theo ngày mua + 12 tháng, admin không cần chọn thủ công.
                </div>
            </div>
        </section>
    </div>
</div>

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
                        {{ $history['time'] }}
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