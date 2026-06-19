@extends('layouts.admin')

@section('title', 'Bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Quản lý bảo hành')
@section('page_subtitle', 'Quản lý phiếu bảo hành, tra cứu IMEI và cập nhật trạng thái bảo hành.')

@section('heading_actions')
<a href="{{ route('admin.warranties.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Tạo phiếu bảo hành
</a>

<a href="{{ route('admin.warranties.lookupImei') }}" class="btn btn-light btn-sm">
    <i class="bi bi-search"></i> Tra cứu IMEI
</a>
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<section class="panel">
    <div class="panel-header">
        <form method="GET" action="{{ route('admin.warranties.index') }}" class="row g-2 flex-grow-1">

            <div class="col-md-6">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="IMEI, mã đơn, tên hoặc SĐT">
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('status')==='active' )>
                        Còn bảo hành
                    </option>
                    <option value="expired" @selected(request('status')==='expired' )>
                        Hết hạn
                    </option>
                    <option value="claimed" @selected(request('status')==='claimed' )>
                        Đang bảo hành
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Tìm kiếm
                </button>

                <a href="{{ route('admin.warranties.index') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>

        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>IMEI</th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($warranties as $warranty)
                <tr>
                    <td class="fw-semibold">
                        {{ $warranty->imei->imei ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $warranty->order->order_code ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $warranty->order->customer_name ?? 'Không có' }}
                    </td>

                    <td>
                        {{ $warranty->warranty_start ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $warranty->warranty_end ?? 'N/A' }}
                    </td>

                    <td>
                        @if($warranty->status === 'active')
                        <span class="badge text-bg-success">
                            Còn bảo hành
                        </span>
                        @elseif($warranty->status === 'expired')
                        <span class="badge text-bg-secondary">
                            Hết hạn
                        </span>
                        @elseif($warranty->status === 'claimed')
                        <span class="badge text-bg-warning">
                            Đang bảo hành
                        </span>
                        @else
                        <span class="badge text-bg-light">
                            {{ $warranty->status }}
                        </span>
                        @endif
                    </td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
                                Chi tiết
                            </a>

                            <a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-light btn-sm">
                                Sửa
                            </a>

                            <form method="POST" action="{{ route('admin.warranties.updateStatus', $warranty) }}" class="d-flex gap-2">
                                @csrf
                                @method('PATCH')

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
                        Chưa có phiếu bảo hành nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($warranties->hasPages())
    <div class="p-3">
        {{ $warranties->withQueryString()->links() }}
    </div>
    @endif
</section>

@endsection