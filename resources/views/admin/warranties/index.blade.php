@extends('layouts.admin')

@section('title', 'Bảo hành')
@section('page_icon', 'bi-shield-check')
@section('page_eyebrow', 'Dịch vụ sau bán')
@section('page_title', 'Quản lý bảo hành')
@section('page_subtitle', 'Quản lý phiếu bảo hành theo IMEI đã bán, lỗi khách báo và trạng thái xử lý.')

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

@if (session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
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

                    <option value="claimed" @selected(request('status') === 'claimed')>
                        Đang xử lý bảo hành
                    </option>

                    <option value="active" @selected(request('status') === 'active')>
                        Hoàn tất xử lý
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
                    <th>Mã phiếu</th>
                    <th>IMEI</th>
                    <th>Sản phẩm</th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Lỗi khách báo</th>
                    <th>Thời hạn bảo hành</th>
                    <th>Trạng thái xử lý</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($warranties as $warranty)
                @php
                    $detail = $warrantyDetails[$warranty->id] ?? null;
                    $isWarrantyExpired = $warranty->warranty_end
                        ? now()->startOfDay()->gt($warranty->warranty_end->copy()->startOfDay())
                        : false;
                @endphp

                <tr>
                    <td class="fw-semibold">
                        {{ $warranty->warranty_code }}
                    </td>

                    <td>
                        {{ $warranty->imei->imei ?? $detail->imei ?? 'Không có' }}
                    </td>

                    <td>
                        @if($detail)
                            <div class="fw-semibold">
                                {{ $detail->product_name }}
                            </div>
                            <div class="text-muted small">
                                {{ $detail->color }} - {{ $detail->storage }}
                            </div>
                        @else
                            <span class="text-muted">Không có</span>
                        @endif
                    </td>

                    <td>
                        {{ $warranty->order->order_code ?? $detail->order_code ?? 'Không có' }}
                    </td>

                    <td>
                        <div>
                            {{ $warranty->order->customer_name ?? $detail->customer_name ?? 'Không có' }}
                        </div>
                        <div class="text-muted small">
                            {{ $warranty->order->customer_phone ?? $detail->customer_phone ?? '' }}
                        </div>
                    </td>

                    <td style="max-width: 220px;">
                        @if($warranty->customer_note)
                            <div class="small">
                                {{ \Illuminate\Support\Str::limit($warranty->customer_note, 80) }}
                            </div>
                        @else
                            <span class="text-muted small">Chưa có ghi chú</span>
                        @endif
                    </td>

                    <td>
                        <div>
                            {{ $warranty->warranty_start ? $warranty->warranty_start->format('d/m/Y') : 'N/A' }}
                        </div>

                        <div class="text-muted small">
                            đến {{ $warranty->warranty_end ? $warranty->warranty_end->format('d/m/Y') : 'N/A' }}
                        </div>

                        @if($isWarrantyExpired)
                            <div class="text-danger small">
                                IMEI đã quá hạn
                            </div>
                        @elseif($warranty->warranty_end)
                            <div class="text-success small">
                                IMEI còn hạn
                            </div>
                        @endif
                    </td>

                    <td>
                        <span class="badge text-bg-{{ $warranty->status_badge }}">
                            {{ $warranty->status_label }}
                        </span>
                    </td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2 flex-wrap">
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
                                    <option value="claimed" @selected($warranty->status === 'claimed')>
                                        Đang xử lý
                                    </option>

                                    <option value="active" @selected(in_array($warranty->status, ['active', 'expired'], true))>
                                        Hoàn tất
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
                    <td colspan="9" class="text-center text-muted py-4">
                        Chưa có phiếu bảo hành nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($warranties->hasPages())
    <div class="p-3">
        {{ $warranties->links() }}
    </div>
    @endif
</section>

@endsection