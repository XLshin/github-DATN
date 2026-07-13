@extends('layouts.app')

@section('title', 'Tra cứu bảo hành')

@section('content')
<div class="container py-4">
    <h1 class="mb-2">Tra cứu bảo hành</h1>
    <p class="text-muted mb-4">Chỉ hiển thị sản phẩm của tài khoản bạn đã có phiếu bảo hành.</p>

    <form method="GET" action="{{ route('warranties.lookup') }}" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label for="warranty-search" class="form-label">Tên sản phẩm</label>
                <input id="warranty-search" name="search" class="form-control" value="{{ $search }}" placeholder="Nhập tên sản phẩm cần tìm">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100">Tìm sản phẩm</button>
            </div>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Phiếu bảo hành</h5>

            @forelse($orderItems as $item)
                @php
                    $warranty = $item->imeis
                        ->pluck('warranty')
                        ->filter()
                        ->sortByDesc('created_at')
                        ->first();
                @endphp

                @if($warranty)
                    <div class="list-group-item border rounded mb-2 p-3">
                        <div class="d-flex justify-content-between align-items-start flex-column flex-md-row gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h6 class="mb-0">{{ $item->product?->name ?? 'Sản phẩm chưa xác định' }}</h6>
                                    <span class="badge text-bg-{{ $warranty->status_badge ?? 'light' }}">
                                        {{ $warranty->status_label ?? '---' }}
                                    </span>
                                </div>

                                <div class="row g-3 small text-muted">
                                    <div class="col-md-6">
                                        <div class="fw-semibold text-dark">Mã phiếu bảo hành</div>
                                        <div>{{ $warranty->warranty_code }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold text-dark">IMEI</div>
                                        <div>{{ $warranty->imei?->imei ?? '---' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold text-dark">Thời gian bảo hành</div>
                                        <div>{{ optional($warranty->warranty_start)->format('d/m/Y') ?? '---' }} - {{ optional($warranty->warranty_end)->format('d/m/Y') ?? '---' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold text-dark">Đơn hàng</div>
                                        <div>{{ $item->order?->order_code ?? '---' }} · {{ optional($item->order?->created_at)->format('d/m/Y') ?? '---' }}</div>
                                    </div>
                                </div>

                                <p class="mb-0 mt-2 text-muted">
                                    Màu: {{ $item->variant?->color ?? 'Mặc định' }}
                                    @if($item->product?->storage)
                                        · Dung lượng: {{ $item->product->storage }}
                                    @endif
                                    · Số lượng: {{ $item->quantity }}
                                </p>
                            </div>

                            <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-outline-primary">
                                Xem bảo hành
                            </a>
                        </div>
                    </div>
                @endif
            @empty
                <div class="alert alert-info mb-0">
                    {{ $search !== '' ? 'Không tìm thấy phiếu bảo hành theo tên sản phẩm đã nhập.' : 'Tài khoản của bạn chưa có phiếu bảo hành nào.' }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
