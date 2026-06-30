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
    <div class="col-lg-6">
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
                        <th>Bộ nhớ</th>
                        <td><span class="badge text-bg-info fs-6">{{ $variant->storage }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <th>Giá thêm</th>
                        <td class="fw-semibold">
                            {{ $variant->additional_price > 0 ? '+'.number_format($variant->additional_price, 0, ',', '.') : '0' }} đ
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
                            <label class="form-label">Bộ nhớ</label>
                            <input type="text" name="storage" class="form-control" value="{{ old('storage', $variant->storage) }}">
                        </div>
                        @else
                        <input type="hidden" name="storage" value="">
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Tồn kho</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" value="{{ old('stock_quantity', $variant->stock_quantity) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá thêm (đ)</label>
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
