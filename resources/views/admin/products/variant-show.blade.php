@extends('layouts.admin')

@section('title', 'Chi tiết biến thể')
@section('page_icon', 'bi-layers')
@section('page_eyebrow', $variant->product->name)
@section('page_title', $variant->color . ($variant->storage ? ' / ' . $variant->storage : ''))
@section('page_subtitle', 'Chi tiết biến thể sản phẩm.')

@section('heading_actions')
<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editVariantModal">
    <i class="bi bi-pencil"></i> Sửa biến thể
</button>
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Thông tin biến thể</h5>
            </div>
            <div class="p-3">
                <table class="table table-borderless align-middle mb-0">
                    <tr>
                        <th style="width:160px;">Sản phẩm</th>
                        <td>
                            <a href="{{ route('admin.products.show', $variant->product) }}" class="text-decoration-none">
                                {{ $variant->product->name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Danh mục</th>
                        <td>{{ $variant->product->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Thương hiệu</th>
                        <td>{{ $variant->product->brand->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Màu sắc</th>
                        <td><span class="badge text-bg-secondary fs-6">{{ $variant->color }}</span></td>
                    </tr>
                    @if($variant->storage)
                    <tr>
                        <th>Kiểu loại</th>
                        <td><span class="badge text-bg-info fs-6">{{ $variant->storage }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <th>Giá sản phẩm</th>
                        <td class="fw-semibold">
                            {{ $variant->additional_price > 0 ? number_format($variant->additional_price, 0, ',', '.') : '0' }} đ
                        </td>
                    </tr>
                    <tr>
                        <th>Tồn kho</th>
                        <td>
                            <span class="fw-semibold {{ $variant->stock_quantity <= 0 ? 'text-danger' : '' }}">
                                {{ $variant->stock_quantity }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
                        <td>
                            @if($variant->status)
                            <span class="badge text-bg-success">Active</span>
                            @else
                            <span class="badge text-bg-secondary">Ẩn</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Ngày tạo</th>
                        <td>{{ $variant->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Cập nhật</th>
                        <td>{{ $variant->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </section>
    </div>
    <div class="col-lg-4">

    @if($variant->product->product_type === 'imei/serial')

        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Danh sách IMEI</h5>
            </div>

            <div class="p-3">

                @if($variant->imeis->count())

                    <div style="max-height:420px;overflow:auto;">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>IMEI</th>
                                    <th>TT</th>
                                    <th>Ngày nhập</th>
                                </tr>
                            </thead>

                            <tbody>

                            @foreach($variant->imeis as $imei)

                                <tr>
                                    <td class="small">{{ $imei->imei }}</td>

                                    <td>
                                        @switch($imei->status)

                                            @case('available')
                                                <span class="badge text-bg-success">
                                                    Available
                                                </span>
                                                @break

                                            @case('sold')
                                                <span class="badge text-bg-danger">
                                                    Sold
                                                </span>
                                                @break

                                            @case('warranty')
                                                <span class="badge text-bg-warning">
                                                    Warranty
                                                </span>
                                                @break

                                            @default
                                                <span class="badge text-bg-secondary">
                                                    {{ $imei->status }}
                                                </span>

                                        @endswitch
                                    </td>
                                    <td class="small">{{ $imei->created_at }}</td>


                                </tr>

                            @endforeach

                            </tbody>

                        </table>
                    </div>

                @else

                    <div class="text-muted">
                        Chưa có IMEI nào.
                    </div>

                @endif

            </div>
        </section>

    @else

        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Lịch sử nhập / xuất</h5>
            </div>

            <div class="p-3">

                @if($variant->inventoryTransactions->count())

                    <div style="max-height:420px;overflow:auto;">

                        @foreach($variant->inventoryTransactions->sortByDesc('created_at') as $log)

                            <div class="border rounded p-2 mb-2">

                                <div class="fw-semibold">

                                    @switch($log->type)

                                        @case('import')
                                            <span class="text-success">
                                                Nhập kho
                                            </span>
                                            @break

                                        @case('export')
                                            <span class="text-danger">
                                                Xuất kho
                                            </span>
                                            @break

                                        @default
                                            <span class="text-primary">
                                                Điều chỉnh
                                            </span>

                                    @endswitch

                                </div>

                                <div>
                                    SL:
                                    <strong>{{ $log->quantity }}</strong>
                                </div>

                                @if($log->note)
                                    <div class="small text-muted">
                                        {{ $log->note }}
                                    </div>
                                @endif

                                <div class="small text-muted">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="text-muted">
                        Chưa có lịch sử nhập xuất.
                    </div>

                @endif

            </div>
        </section>

    @endif

</div>
</div>

{{-- Modal sửa biến thể --}}
<div class="modal fade" id="editVariantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.variants.update', $variant) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Sửa biến thể</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Màu <span class="text-danger">*</span></label>
                            <input type="text" name="color" class="form-control" value="{{ old('color', $variant->color) }}" required>
                        </div>
                        @if($variant->storage !== null && $variant->storage !== '')
                        <div class="col-md-6">
                            <label class="form-label">Kiểu loại</label>
                            <input type="text" name="storage" class="form-control" value="{{ old('storage', $variant->storage) }}">
                        </div>
                        @else
                        <input type="hidden" name="storage" value="">
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Giá sản phẩm(đ)</label>
                            <input type="number" name="additional_price" class="form-control" min="0" value="{{ old('additional_price', $variant->additional_price) }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="vStatus" value="1"
                                    {{ old('status', $variant->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="vStatus">Đang bán</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
