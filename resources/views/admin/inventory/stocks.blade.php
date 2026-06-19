@extends('layouts.admin')

@section('title', 'Tồn kho')
@section('page_icon', 'bi-boxes')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Tồn kho')
@section('page_subtitle', 'Theo dõi số lượng tồn kho theo từng biến thể sản phẩm.')

@section('heading_actions')
<a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại lịch sử kho
</a>
@endsection

@section('content')
<section class="panel mb-4">
    <div class="panel-header">
        <form method="GET" class="row g-2 flex-grow-1">
            <div class="col-md-6">
                <input
                    type="number"
                    name="variant_id"
                    value="{{ request('variant_id') }}"
                    class="form-control form-control-sm"
                    placeholder="Nhập Variant ID">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Tìm kiếm
                </button>

                <a href="{{ route('stocks') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="panel p-3">
            <div class="text-muted small">Tổng Variant</div>
            <div class="fs-4 fw-semibold">
                {{ $stocks->count() }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel p-3">
            <div class="text-muted small">Hết hàng</div>
            <div class="fs-4 fw-semibold">
                {{ $stocks->where('total', 0)->count() }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel p-3">
            <div class="text-muted small">Sắp hết</div>
            <div class="fs-4 fw-semibold">
                {{ $stocks->filter(fn($item) => $item->total > 0 && $item->total < 5)->count() }}
            </div>
        </div>
    </div>
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Màu sắc</th>
                    <th>Dung lượng</th>
                    <th class="text-end">Tồn kho</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>

            <tbody>
                @forelse($stocks as $stock)
                <tr>
                    <td class="fw-semibold">
                        {{ $stock->productVariant?->product?->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $stock->productVariant?->color ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $stock->productVariant?->storage ?? 'N/A' }}
                    </td>

                    <td class="text-end fw-semibold">
                        {{ $stock->total }}
                    </td>

                    <td>
                        @if($stock->total <= 0)
                            <span class="badge text-bg-danger">
                            Hết hàng
                            </span>
                            @elseif($stock->total < 5)
                                <span class="badge text-bg-warning">
                                Sắp hết
                                </span>
                                @else
                                <span class="badge text-bg-success">
                                    Còn hàng
                                </span>
                                @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Không có dữ liệu
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection