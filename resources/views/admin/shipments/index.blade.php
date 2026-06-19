@extends('layouts.admin')

@section('title', 'Vận chuyển')
@section('page_icon', 'bi-truck')
@section('page_eyebrow', 'Quản lý đơn hàng')
@section('page_title', 'Quản lý vận chuyển')
@section('page_subtitle', 'Tạo vận đơn, theo dõi trạng thái giao hàng và cập nhật tiến trình vận chuyển.')

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<section class="panel mb-4">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Đơn hàng có thể tạo vận đơn</h5>
            <div class="text-muted small">
                Danh sách đơn hàng đang chờ tạo vận đơn.
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Địa chỉ</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ordersCanCreateShipment as $order)
                <tr>
                    <td class="fw-semibold">
                        {{ $order->order_code }}
                    </td>

                    <td>
                        {{ $order->customer_name }}
                    </td>

                    <td>
                        {{ $order->customer_phone }}
                    </td>

                    <td>
                        {{ $order->shipping_address }}
                    </td>

                    <td>
                        <span class="badge text-bg-secondary">
                            {{ $order->status }}
                        </span>
                    </td>

                    <td class="text-end">
                        <a href="{{ route('admin.shipments.createFromOrder', $order) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg"></i> Tạo vận đơn
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Không có đơn hàng nào đang chờ tạo vận đơn.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <form method="GET" action="{{ route('admin.shipments.index') }}" class="row g-2 flex-grow-1">

            <div class="col-md-6">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Mã vận đơn, mã đơn, tên hoặc SĐT">
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" @selected(request('status')==='pending' )>Chờ giao</option>
                    <option value="shipping" @selected(request('status')==='shipping' )>Đang giao</option>
                    <option value="delivered" @selected(request('status')==='delivered' )>Đã giao</option>
                    <option value="failed" @selected(request('status')==='failed' )>Giao thất bại</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Tìm kiếm
                </button>

                <a href="{{ route('admin.shipments.index') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>

        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Đơn vị vận chuyển</th>
                    <th>Mã vận đơn</th>
                    <th>Trạng thái giao hàng</th>
                    <th>Ngày tạo</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($shipments as $shipment)
                <tr>
                    <td class="fw-semibold">
                        {{ $shipment->order->order_code ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $shipment->order->customer_name ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $shipment->shipping_unit }}
                    </td>

                    <td>
                        {{ $shipment->tracking_code ?? 'Chưa có' }}
                    </td>

                    <td>
                        @if($shipment->shipping_status === 'pending')
                        <span class="badge text-bg-secondary">Chờ giao</span>
                        @elseif($shipment->shipping_status === 'shipping')
                        <span class="badge text-bg-primary">Đang giao</span>
                        @elseif($shipment->shipping_status === 'delivered')
                        <span class="badge text-bg-success">Đã giao</span>
                        @elseif($shipment->shipping_status === 'failed')
                        <span class="badge text-bg-danger">Giao thất bại</span>
                        @else
                        <span class="badge text-bg-light">{{ $shipment->shipping_status }}</span>
                        @endif
                    </td>

                    <td>
                        {{ $shipment->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                    </td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.shipments.show', $shipment) }}" class="btn btn-light btn-sm">
                                Chi tiết
                            </a>

                            <form method="POST" action="{{ route('admin.shipments.updateStatus', $shipment) }}" class="d-flex gap-2">
                                @csrf
                                @method('PATCH')

                                <select name="shipping_status" class="form-select form-select-sm">
                                    <option value="pending" @selected($shipment->shipping_status === 'pending')>Chờ giao</option>
                                    <option value="shipping" @selected($shipment->shipping_status === 'shipping')>Đang giao</option>
                                    <option value="delivered" @selected($shipment->shipping_status === 'delivered')>Đã giao</option>
                                    <option value="failed" @selected($shipment->shipping_status === 'failed')>Giao thất bại</option>
                                </select>

                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    Cập nhật
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Chưa có vận đơn nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($shipments->hasPages())
    <div class="p-3">
        {{ $shipments->withQueryString()->links() }}
    </div>
    @endif
</section>

@endsection