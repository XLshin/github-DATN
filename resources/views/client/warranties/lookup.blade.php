@extends('layouts.app')

@section('title', 'Tra cứu bảo hành')

@section('content')
<div class="container py-4">
    <h1 class="mb-2">Tra cứu bảo hành</h1>

    <p class="text-muted mb-4">
        Hiển thị tất cả phiếu bảo hành của các sản phẩm thuộc tài khoản của bạn.
    </p>

    <form
        method="GET"
        action="{{ route('warranties.lookup') }}"
        class="mb-4"
    >
        <div class="row g-2 align-items-end">
            <div class="col-md-8">
                <label
                    for="warranty-search"
                    class="form-label"
                >
                    Tên sản phẩm
                </label>

                <input
                    id="warranty-search"
                    type="text"
                    name="search"
                    class="form-control"
                    value="{{ $search ?? '' }}"
                    placeholder="Nhập tên sản phẩm cần tìm"
                >
            </div>

            <div class="col-md-4">
                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    Tìm sản phẩm
                </button>
            </div>
        </div>
    </form>

    @php
        /*
         * Gom toàn bộ phiếu bảo hành của tất cả IMEI.
         *
         * Mỗi phần tử gồm:
         * - item: sản phẩm trong đơn hàng
         * - warranty: phiếu bảo hành
         */
        $warrantyRows = collect();

        foreach ($orderItems as $item) {
            foreach ($item->imeis as $imei) {
                foreach ($imei->warranties as $warranty) {
                    $warrantyRows->push([
                        'item' => $item,
                        'warranty' => $warranty,
                    ]);
                }
            }
        }

        /*
         * Loại bỏ phiếu bị trùng nếu dữ liệu quan hệ trả về lặp,
         * sau đó sắp xếp phiếu mới nhất lên đầu.
         */
        $warrantyRows = $warrantyRows
            ->unique(function ($row) {
                return $row['warranty']->id;
            })
            ->sortByDesc(function ($row) {
                return $row['warranty']->created_at;
            })
            ->values();
    @endphp

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                Phiếu bảo hành
            </h5>

            @forelse($warrantyRows as $row)
                @php
                    $item = $row['item'];
                    $warranty = $row['warranty'];
                @endphp

                <div class="list-group-item border rounded mb-3 p-3">
                    <div
                        class="d-flex justify-content-between align-items-start flex-column flex-md-row gap-3"
                    >
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                <h6 class="mb-0">
                                    {{ $item->product?->name ?? 'Sản phẩm chưa xác định' }}
                                </h6>

                                <span
                                    class="badge text-bg-{{ $warranty->status_badge ?? 'light' }}"
                                >
                                    {{ $warranty->status_label ?? 'Chưa xác định' }}
                                </span>

                                @if($loop->first)
                                    <span class="badge text-bg-primary">
                                        Phiếu mới nhất
                                    </span>
                                @endif
                            </div>

                            <div class="row g-3 small text-muted">
                                <div class="col-md-6">
                                    <div class="fw-semibold text-dark">
                                        Mã phiếu bảo hành
                                    </div>

                                    <div class="fw-bold text-primary">
                                        {{ $warranty->warranty_code }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold text-dark">
                                        IMEI
                                    </div>

                                    <div>
                                        {{ $warranty->imei?->imei ?? '---' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold text-dark">
                                        Thời gian bảo hành
                                    </div>

                                    <div>
                                        {{ optional($warranty->warranty_start)->format('d/m/Y') ?? '---' }}
                                        -
                                        {{ optional($warranty->warranty_end)->format('d/m/Y') ?? '---' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold text-dark">
                                        Ngày tạo phiếu
                                    </div>

                                    <div>
                                        {{ optional($warranty->created_at)->format('d/m/Y H:i') ?? '---' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold text-dark">
                                        Đơn hàng
                                    </div>

                                    <div>
                                        {{ $item->order?->order_code ?? '---' }}
                                        ·
                                        {{ optional($item->order?->created_at)->format('d/m/Y') ?? '---' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fw-semibold text-dark">
                                        Khách hàng
                                    </div>

                                    <div>
                                        {{ $warranty->order?->customer_name
                                            ?? $item->order?->customer_name
                                            ?? '---' }}
                                    </div>
                                </div>
                            </div>

                            <p class="mb-0 mt-3 text-muted">
                                Màu:
                                {{ $item->variant?->color ?? 'Mặc định' }}

                                @if($item->product?->storage)
                                    · Dung lượng:
                                    {{ $item->product->storage }}
                                @endif

                                · Số lượng:
                                {{ $item->quantity }}
                            </p>

                            @if(filled($warranty->customer_note))
                                <div class="mt-3 p-2 bg-light border rounded">
                                    <div class="small fw-semibold text-dark">
                                        Lỗi khách hàng báo
                                    </div>

                                    <div class="small text-muted mt-1">
                                        {{ $warranty->customer_note }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <a
                                href="{{ route('warranties.show', $warranty) }}"
                                class="btn btn-outline-primary"
                            >
                                Xem bảo hành
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info mb-0">
                    @if(($search ?? '') !== '')
                        Không tìm thấy phiếu bảo hành theo tên sản phẩm đã nhập.
                    @else
                        Tài khoản của bạn chưa có phiếu bảo hành nào.
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection