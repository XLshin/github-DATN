@extends('layouts.admin')

@section('title', 'Chi tiết biến thể')
@section('page_icon', 'bi-layers')
@section('page_eyebrow', $variant->product->name)
@section('page_title', $variant->color . ($variant->product->storage ? ' / ' . $variant->product->storage : ''))
@section('page_subtitle', 'Chi tiết biến thể sản phẩm.')

@section('heading_actions')
<a href="#" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editVariantModal">
    <i class="bi bi-pencil"></i> Sửa biến thể
</a>
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
                    @if($variant->product->storage)
                    <tr>
                        <th>Bộ nhớ</th>
                        <td><span class="badge text-bg-info fs-6">{{ $variant->product->storage }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <th>Giá của biến thể</th>
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
                    @if($variant->product->product_type === 'imei/serial')
                    <tr>
                        <th>Số IMEI</th>
                        <td>
                            <span class="fw-semibold">{{ $variant->imeis->count() }}</span>
                        </td>
                    </tr>
                    @endif
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

    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Ảnh biến thể</h5>
            </div>
            <div class="p-3">
                @php
                    $variantImages = collect();
                    if ($variant->image_path) {
                        $variantImages->push($variant->image_path);
                    }
                    $variantImages = $variantImages->merge($variant->images->pluck('image_path'));
                @endphp

                @if($variantImages->isNotEmpty())
                <div class="row g-2">
                    @if($variant->image_path)
                    <div class="col-6">
                        <div class="position-relative">
                            <img src="{{ Storage::url($variant->image_path) }}" alt="Ảnh chính biến thể" class="img-fluid rounded border" style="max-height: 220px; object-fit: cover; width: 100%;">
                            <form action="{{ route('admin.variants.image.destroy', $variant) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa ảnh chính">×</button>
                            </form>
                        </div>
                    </div>
                    @endif

                    @foreach($variant->images as $image)
                    <div class="col-6">
                        <div class="position-relative">
                            <img src="{{ Storage::url($image->image_path) }}" alt="Ảnh phụ biến thể" class="img-fluid rounded border" style="max-height: 220px; object-fit: cover; width: 100%;">
<form action="{{ route('admin.products.image.destroy', $image) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa ảnh">×</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-muted">Chưa có ảnh biến thể</div>
                @endif
            </div>
        </section>
    </div>
</div>

@if($variant->product->product_type === 'imei/serial' && $variant->imeis->isNotEmpty())
        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Danh sách IMEI / Serial</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>IMEI / Serial</th>
                            <th>Trạng thái</th>
                            <th>Ngày thêm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($variant->imeis as $imei)
                        <tr>
                            <td class="fw-semibold">{{ $imei->imei }}</td>
                            <td>
                                @if($imei->status === 'available')
                                <span class="badge text-bg-success">Sẵn sàng</span>
                                @elseif($imei->status === 'sold')
                                <span class="badge text-bg-danger">Đã bán</span>
                                @elseif($imei->status === 'warranty')
                                <span class="badge text-bg-warning text-dark">Bảo hành</span>
                                @else
                                <span class="badge text-bg-secondary">{{ ucfirst($imei->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $imei->created_at ? $imei->created_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif
    </div>
</div>

{{-- Modal sửa biến thể --}}
<div class="modal fade" id="editVariantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.variants.update', $variant) }}" method="POST" enctype="multipart/form-data">
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
                        @if($variant->product->storage !== null && $variant->product->storage !== '')
                        <div class="col-md-6">
                            <label class="form-label">Bộ nhớ</label>
                            <input type="text" name="storage" class="form-control" value="{{ old('storage', $variant->product->storage) }}">
                        </div>
                        @else
                        <input type="hidden" name="storage" value="">
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Ảnh biến thể</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <div class="form-text small">Chọn nhiều ảnh mới để cập nhật và thêm vào gallery.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá của biến thể (đ)</label>
                            <input type="number" name="additional_price" class="form-control" min="0" value="{{ old('additional_price', $variant->additional_price) }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="vStatus" value="1"
                                    {{ old('status', $variant->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="vStatus">Đang bán</label>
                            </div>
                        </div>
                        @if($variant->image_path)
                        <div class="col-12">
                            <div class="border rounded p-2">
                                <label class="form-label">Ảnh hiện tại</label>
                                <div>
                                    <img src="{{ Storage::url($variant->image_path) }}" alt="Ảnh biến thể" class="img-fluid rounded" style="max-height: 180px; object-fit: cover;">
</div>
                            </div>
                        </div>
                        @endif
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