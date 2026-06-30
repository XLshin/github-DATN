@extends('layouts.admin')

@section('title', 'Chi tiết vận đơn')
@section('page_icon', 'bi-truck')
@section('page_eyebrow', 'Quản lý vận chuyển')
@section('page_title', 'Chi tiết vận đơn')
@section('page_subtitle', 'Xem thông tin vận chuyển, đơn hàng, trạng thái giao hàng và lịch sử vận chuyển.')

@section('heading_actions')
<a href="{{ route('admin.shipments.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
</a>
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin vận chuyển</h5>
                    <div class="text-muted small">
                        Thông tin mã vận đơn, đơn vị vận chuyển và tiến trình giao hàng.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã vận đơn</div>
                    <div class="fw-semibold">
                        {{ $shipment->tracking_code ?? 'Chưa có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Đơn vị vận chuyển</div>
                    <div class="fw-semibold">
                        {{ $shipment->shipping_unit }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Trạng thái</div>

                    @if($shipment->shipping_status === 'pending')
                    <span class="badge text-bg-secondary">Chờ giao</span>
                    @elseif($shipment->shipping_status === 'shipping')
                    <span class="badge text-bg-primary">Đang giao</span>
                    @elseif($shipment->shipping_status === 'delivered')
                    <span class="badge text-bg-success">Đã giao</span>
                    @elseif($shipment->shipping_status === 'failed')
                    <span class="badge text-bg-danger">Giao thất bại</span>
                    @else
                    <span class="badge text-bg-light">
                        {{ $shipment->shipping_status }}
                    </span>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Ngày bắt đầu giao</div>
                    <div class="fw-semibold">
                        {{ $shipment->shipped_at ?? 'Chưa có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Ngày giao thành công</div>
                    <div class="fw-semibold">
                        {{ $shipment->delivered_at ?? 'Chưa có' }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin đơn hàng</h5>
                    <div class="text-muted small">
                        Thông tin khách hàng và địa chỉ giao hàng.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-3">
                    <div class="text-muted small">Mã đơn</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->order_code ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Khách hàng</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->customer_name ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">SĐT</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->customer_phone ?? 'Không có' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Địa chỉ</div>
                    <div class="fw-semibold">
                        {{ $shipment->order->shipping_address ?? 'Không có' }}
                    </div>
                </div>

                <div>
                    <div class="text-muted small">Trạng thái đơn</div>
                    <span class="badge text-bg-secondary">
                        {{ $shipment->order->status ?? 'Không có' }}
                    </span>
                </div>
            </div>
        </section>
    </div>
</div>

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Hình ảnh vận chuyển</h5>
            <div class="text-muted small">
                Hình ảnh xuất kho và giao hàng thành công.
            </div>
        </div>
    </div>

    <div class="p-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small mb-2">Hình ảnh xuất kho</div>
                @if($shipment->shipped_image)
                    <img src="{{ asset('storage/' . $shipment->shipped_image) }}" alt="Hình ảnh xuất kho" class="img-fluid rounded" style="max-width: 100%;">
                @else
                    <div class="text-muted p-4 text-center" style="background: #f5f5f5; border-radius: 4px;">
                        Chưa có hình ảnh
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="text-muted small mb-2">Hình ảnh giao hàng thành công</div>
                @if($shipment->delivered_image)
                    <img src="{{ asset('storage/' . $shipment->delivered_image) }}" alt="Hình ảnh giao hàng" class="img-fluid rounded" style="max-width: 100%;">
                @else
                    <div class="text-muted p-4 text-center" style="background: #f5f5f5; border-radius: 4px;">
                        Chưa có hình ảnh
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Cập nhật trạng thái giao hàng</h5>
            <div class="text-muted small">
                Thay đổi trạng thái hiện tại của vận đơn.
            </div>
        </div>
    </div>

    <div class="p-3">
        @if(in_array($shipment->shipping_status, ['delivered', 'failed']))
            <div class="alert alert-warning mb-0">
                Vận đơn đã ở trạng thái cuối cùng và không thể cập nhật tiếp.
            </div>
        @else
            <form method="POST" action="{{ route('admin.shipments.updateStatus', $shipment) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">
                            Trạng thái giao hàng
                        </label>

                        <select name="shipping_status" class="form-select form-select-sm show-status-select">
                            <option value="pending" @selected($shipment->shipping_status === 'pending')>
                                Chờ giao
                            </option>

                            <option value="shipping" @selected($shipment->shipping_status === 'shipping')>
                                Đang giao
                            </option>

                            <option value="delivered" @selected($shipment->shipping_status === 'delivered')>
                                Đã giao
                            </option>

                            <option value="failed" @selected($shipment->shipping_status === 'failed')>
                                Giao thất bại
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-5 shipped-image-group-show d-none">
                        <label class="form-label">
                            Hình ảnh xuất kho <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="shipped_image" accept="image/*" class="form-control form-control-sm" required>
                        <small class="text-muted">Định dạng: JPEG, PNG, GIF (tối đa 5MB)</small>
                    </div>

                    <div class="col-md-5 delivered-image-group-show d-none">
                        <label class="form-label">
                            Hình ảnh giao hàng thành công <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="delivered_image" accept="image/*" class="form-control form-control-sm" required>
                        <small class="text-muted">Định dạng: JPEG, PNG, GIF (tối đa 5MB)</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg"></i> Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Lịch sử vận chuyển</h5>
            <div class="text-muted small">
                Các mốc thời gian và sự kiện trong quá trình vận chuyển.
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
                        Chưa có lịch sử vận chuyển.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.querySelector('.show-status-select');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const status = this.value;
            const shippedGroup = document.querySelector('.shipped-image-group-show');
            const deliveredGroup = document.querySelector('.delivered-image-group-show');
            
            if (shippedGroup) {
                shippedGroup.classList.toggle('d-none', status !== 'shipping');
                const input = shippedGroup.querySelector('input');
                if (input) input.required = status === 'shipping';
            }
            
            if (deliveredGroup) {
                deliveredGroup.classList.toggle('d-none', status !== 'delivered');
                const input = deliveredGroup.querySelector('input');
                if (input) input.required = status === 'delivered';
            }
        });
    }
});
</script>

@endsection
