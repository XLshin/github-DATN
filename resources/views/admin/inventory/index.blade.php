@extends('layouts.admin')

@section('title', 'Lịch sử kho')
@section('page_icon', 'bi-clock-history')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Lịch sử kho')
@section('page_subtitle', 'Theo dõi lịch sử nhập kho, xuất kho, trả kho và điều chỉnh tồn kho.')

@section('heading_actions')
<a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
    <i class="bi bi-box-seam"></i> Kiểm kho
</a>
@endsection

@section('content')
@php
    $typeLabels = [
        'import' => ['label' => 'Nhập kho', 'class' => 'text-bg-success', 'sign' => '+'],
        'export' => ['label' => 'Xuất kho', 'class' => 'text-bg-danger', 'sign' => '-'],
        'return' => ['label' => 'Trả kho', 'class' => 'text-bg-info', 'sign' => '+'],
        'adjustment' => ['label' => 'Điều chỉnh', 'class' => 'text-bg-warning', 'sign' => null],
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
                    placeholder="Tên sản phẩm, dung lượng, màu, thương hiệu, ghi chú hoặc ID biến thể">
            </div>

            <div class="col-md-4">
                <select name="transaction_type" class="form-select form-select-sm">
                    <option value="">Tất cả giao dịch</option>
                    <option value="import" @selected(request('transaction_type') === 'import')>Nhập kho</option>
                    <option value="export" @selected(request('transaction_type') === 'export')>Xuất kho</option>
                    <option value="return" @selected(request('transaction_type') === 'return')>Trả kho</option>
                    <option value="adjustment" @selected(request('transaction_type') === 'adjustment')>Điều chỉnh</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Tìm kiếm
                </button>

                <a href="{{ route('admin.inventory.index') }}" class="btn btn-light btn-sm">
                    Làm mới
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Dung lượng</th>
                    <th>Thương hiệu</th>
                    <th>Màu</th>
                    <th>Loại giao dịch</th>
                    <th class="text-end">Số lượng</th>
                    <th>Ghi chú</th>
                    <th>Thời gian</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($transactions as $item)
                    @php
                        $type = $typeLabels[$item->type] ?? [
                            'label' => $item->type ?: 'Không xác định',
                            'class' => 'text-bg-secondary',
                            'sign' => '',
                        ];
                    @endphp
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->productVariant?->product?->name ?? 'N/A' }}</td>
                        <td>{{ $item->productVariant?->product?->storage ?? '-' }}</td>
                        <td>{{ $item->productVariant?->product?->brand?->name ?? 'N/A' }}</td>
                        <td>{{ $item->productVariant?->color ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $type['class'] }}">
                                {{ $type['label'] }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            @if($item->type === 'adjustment')
                                {{ (int) $item->quantity > 0 ? '+' : '' }}{{ number_format((int) $item->quantity, 0, ',', '.') }}
                            @else
                                {{ $type['sign'] }}{{ number_format(abs((int) $item->quantity), 0, ',', '.') }}
                            @endif
                        </td>
                        <td>{{ $item->note ?? 'N/A' }}</td>
                        <td>{{ $item->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-sm btn-light">
                                Sửa ghi chú
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Không có dữ liệu
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="p-3">
            {{ $transactions->withQueryString()->links() }}
        </div>
    @endif
</section>
@endsection
