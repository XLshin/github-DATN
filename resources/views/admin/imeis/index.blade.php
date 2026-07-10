@extends('layouts.admin')

@section('title', 'IMEI')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho IMEI/Serial')
@section('page_title', 'Danh sách IMEI/Serial')
@section('page_subtitle', 'Quản lý mã IMEI/Serial thiết bị. Chỉ hiển thị IMEI/Serial của sản phẩm thuộc danh mục Điện thoại.')

@section('heading_actions')
    <a href="{{ route('admin.imeis.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Thêm IMEI</a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <form method="GET" class="row g-2 flex-grow-1">
                <div class="col-md-5">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Tìm IMEI...">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tất cả trạng thái</option>
                        <option value="available" @selected(request('status') === 'available')>Available</option>
                        <option value="sold" @selected(request('status') === 'sold')>Sold</option>
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
                        <th>ID</th><th>Sản phẩm</th><th>Màu</th><th>Dung lượng</th><th>IMEI/Serial</th><th>Trạng thái</th><th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imeis as $imei)
                        <tr>
                            <td>{{ $imei->id }}</td>
                            <td>{{ $imei->productVariant->product->name ?? 'N/A' }}</td>
                            <td>{{ $imei->productVariant->color ?? 'N/A' }}</td>
                            <td>{{ $imei->productVariant->product->storage ?? 'N/A' }}</td>
                            <td class="fw-semibold">{{ $imei->imei }}</td>
                            <td><span class="badge text-bg-secondary">{{ $imei->status }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.imeis.edit', $imei->id) }}" class="btn btn-light btn-sm">Sửa</a>
                                <form action="{{ route('admin.imeis.destroy', $imei->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa IMEI?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($imeis->hasPages())<div class="p-3">{{ $imeis->withQueryString()->links() }}</div>@endif
    </section>
@endsection
