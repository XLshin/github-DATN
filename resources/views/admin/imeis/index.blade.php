@extends('layouts.admin')

@section('title', 'IMEI')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho IMEI/Serial')
@section('page_title', 'Tra cứu IMEI/Serial')
@section('page_subtitle', 'Tra cứu và điều chỉnh IMEI khi nhập nhầm. Không xóa cứng IMEI khỏi hệ thống.')

@section('heading_actions')
    @if(auth()->user()?->isAdmin())
    <a href="{{ route('admin.imeis.bulk-transfer.create') }}" class="btn btn-warning btn-sm">
        <i class="bi bi-arrow-left-right"></i> Chuyển IMEI hàng loạt
    </a>
    @endif
    <a href="{{ route('admin.imeis.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nhập IMEI
    </a>
    <a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
        <i class="bi bi-boxes"></i> Kho IMEI/Serial
    </a>
@endsection

@section('content')
@php
    $statusLabels = [
        'available' => ['label' => 'Còn hàng', 'class' => 'text-bg-success'],
        'reserved' => ['label' => 'Đang giữ chỗ', 'class' => 'text-bg-warning'],
        'sold' => ['label' => 'Đã bán', 'class' => 'text-bg-danger'],
        'warranty' => ['label' => 'Bảo hành', 'class' => 'text-bg-primary'],
        'returned' => ['label' => 'Đã loại khỏi kho', 'class' => 'text-bg-secondary'],
    ];
@endphp

<section class="panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 flex-grow-1">
            <div class="col-md-5">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm IMEI, sản phẩm, dung lượng hoặc màu">
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="available" @selected(request('status') === 'available')>Còn hàng</option>
                    <option value="reserved" @selected(request('status') === 'reserved')>Đang giữ chỗ</option>
                    <option value="sold" @selected(request('status') === 'sold')>Đã bán</option>
                    <option value="warranty" @selected(request('status') === 'warranty')>Bảo hành</option>
                    <option value="returned" @selected(request('status') === 'returned')>Đã loại khỏi kho</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">Tìm</button>
                <a href="{{ route('admin.imeis.index') }}" class="btn btn-light btn-sm">Làm mới</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Màu</th>
                    <th>Dung lượng</th>
                    <th>IMEI/Serial</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($imeis as $imei)
                    @php
                        $status = $statusLabels[$imei->status] ?? ['label' => $imei->status, 'class' => 'text-bg-secondary'];
                    @endphp
                    <tr>
                        <td>{{ $imei->id }}</td>
                        <td>{{ $imei->productVariant?->product?->name ?? 'N/A' }}</td>
                        <td>{{ $imei->productVariant?->color ?? 'N/A' }}</td>
                        <td>{{ $imei->productVariant?->product?->storage ?? 'N/A' }}</td>
                        <td class="fw-semibold">{{ $imei->imei }}</td>
                        <td>
                            <span class="badge {{ $status['class'] }}">
                                {{ $status['label'] }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.imeis.show', $imei->id) }}" class="btn btn-outline-primary btn-sm">
                                Chi tiết
                            </a>
                            @if(auth()->user()?->isAdmin())
                            <a href="{{ route('admin.imeis.edit', $imei->id) }}" class="btn btn-light btn-sm">
                                Điều chỉnh
                            </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Không có dữ liệu
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($imeis->hasPages())
        <div class="p-3">
            {{ $imeis->withQueryString()->links() }}
        </div>
    @endif
</section>
@endsection
