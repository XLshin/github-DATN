@extends('layouts.admin')

@section('title', 'Lịch sử kho')
@section('page_icon', 'bi-clock-history')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Lịch sử kho')
@section('page_subtitle', 'Theo dõi lịch sử nhập kho, xuất kho và điều chỉnh tồn kho.')

@section('heading_actions')

<a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm">
    <i class="bi bi-box-seam"></i> Kiểm kho
</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 flex-grow-1">

            <div class="col-md-5">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Nhập tên sản phẩm hoặc ID biến thể">
            </div>

            <div class="col-md-4">
                <select name="transaction_type" class="form-select form-select-sm">
                    <option value="">Tất cả giao dịch</option>

                    <option value="import" @selected(request('transaction_type')==='import' )>
                        Nhập kho
                    </option>

                    <option value="export" @selected(request('transaction_type')==='export' )>
                        Xuất kho
                    </option>

                    <option value="adjustment" @selected(request('transaction_type')==='adjustment' )>
                        Điều chỉnh
                    </option>
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
                    <th>Màu</th>
                    <th>Dung lượng</th>
                    <th>Loại giao dịch</th>
                    <th class="text-end">Số lượng</th>
                    <th>Ghi chú</th>
                    <th>Thời gian</th>
                    {{-- <th class="text-end">Thao tác</th> --}}
                </tr>
            </thead>

            <tbody>
                @forelse($transactions as $item)
                <tr>
                    <td>{{ $item->id }}</td>

                    <td>
                        {{ $item->productVariant?->product?->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $item->productVariant?->color ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $item->productVariant?->storage ?? 'N/A' }}
                    </td>

                    <td>
                        @if($item->type === 'import')
                        <span class="badge text-bg-success">Nhập kho</span>
                        @elseif($item->type === 'export')
                        <span class="badge text-bg-danger">Xuất kho</span>
                        @elseif($item->type === 'adjustment')
                        <span class="badge text-bg-warning">Điều chỉnh</span>
                        @else
                        <span class="badge text-bg-secondary">Không xác định</span>
                        @endif
                    </td>

                    <td class="text-end fw-semibold">
                        {{ $item->quantity }}
                    </td>

                    <td>
                        {{ $item->note ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $item->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                    </td>

                    {{-- <td class="text-end">
                        <a href="{{ route('admin.inventory.edit', $item->id) }}" class="btn btn-light btn-sm">
                            Sửa
                        </a>
                    </td> --}}
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
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