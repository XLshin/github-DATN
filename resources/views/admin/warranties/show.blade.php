@extends('layouts.admin')

@section('title', 'Chi tiết bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Chi tiết bảo hành')
@section('page_subtitle', 'Xem thông tin phiếu bảo hành, cập nhật trạng thái và theo dõi lịch sử bảo hành.')

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

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin phiếu</h5>
                    <div class="text-muted small">
                        Thông tin IMEI, đơn hàng và thời hạn bảo hành.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">IMEI</div>
                        <div class="fw-semibold">
                            {{ $warranty->imei->imei ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Mã đơn</div>
                        <div class="fw-semibold">
                            {{ $warranty->order->order_code ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Khách hàng</div>
                        <div class="fw-semibold">
                            {{ $warranty->order->customer_name ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">SĐT</div>
                        <div class="fw-semibold">
                            {{ $warranty->order->customer_phone ?? 'Không có' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Ngày bắt đầu</div>
                        <div class="fw-semibold">
                            {{ $warranty->warranty_start }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Ngày kết thúc</div>
                        <div class="fw-semibold">
                            {{ $warranty->warranty_end }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted small">Trạng thái</div>

                        @if($warranty->status === 'active')
                        <span class="badge text-bg-success">Còn bảo hành</span>
                        @elseif($warranty->status === 'expired')
                        <span class="badge text-bg-secondary">Hết hạn</span>
                        @elseif($warranty->status === 'claimed')
                        <span class="badge text-bg-warning">Đang bảo hành</span>
                        @else
                        <span class="badge text-bg-light">
                            {{ $warranty->status }}
                        </span>
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
                        Thay đổi trạng thái hiện tại của phiếu bảo hành.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.warranties.updateStatus', $warranty) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">
                            Trạng thái bảo hành
                        </label>

                        <select name="status" class="form-select form-select-sm">
                            <option value="active" @selected($warranty->status === 'active')>
                                Còn bảo hành
                            </option>

                            <option value="expired" @selected($warranty->status === 'expired')>
                                Hết hạn
                            </option>

                            <option value="claimed" @selected($warranty->status === 'claimed')>
                                Đang bảo hành
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg"></i> Cập nhật
                    </button>
                </form>
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