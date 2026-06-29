@extends('layouts.admin')

@section('title', $product->name)
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', $product->name)
@section('page_subtitle', 'Chi tiết sản phẩm và danh sách biến thể.')

@section('heading_actions')
<a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-pencil"></i> Sửa sản phẩm
</a>
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<div class="row g-3">
    {{-- Cột trái: thông tin + biến thể --}}
    <div class="col-lg-8">
        <section class="panel mb-3">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin sản phẩm</h5>
                </div>
            </div>
            <div class="p-3">
                <table class="table table-borderless align-middle mb-0">
                    <tr>
                        <th style="width:160px;">Danh mục</th>
                        <td>{{ $product->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Thương hiệu</th>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Loại sản phẩm</th>
                        <td>
                            @if($product->product_type === 'imei/serial')
                            <span class="badge text-bg-warning text-dark">IMEI/Serial</span>
                            @else
                            <span class="badge text-bg-secondary">Theo số lượng</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Slug</th>
                        <td><code>{{ $product->slug }}</code></td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
                        <td>
                            @if($product->status)
                            <span class="badge text-bg-success">Đang bán</span>
                            @else
                            <span class="badge text-bg-secondary">Ẩn</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Mô tả</th>
                        <td>{{ $product->description }}</td>
                    </tr>
                </table>
            </div>
        </section>

        {{-- Bảng biến thể --}}
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Biến thể sản phẩm</h5>
                    <div class="text-muted small">{{ $product->variants->count() }} biến thể</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Màu</th>
                            <th>Bộ nhớ</th>
                            <th class="text-end">Giá thêm</th>
                            <th class="text-end">
                                @if($product->product_type === 'imei/serial') Số IMEI @else Tồn kho @endif
                            </th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->variants as $v)
                        <tr>
                            <td><span class="badge text-bg-secondary">{{ $v->color }}</span></td>
                            <td><span class="badge text-bg-info">{{ $v->storage }}</span></td>
                            <td class="text-end fw-semibold">
                                {{ $v->additional_price > 0 ? '+'.number_format($v->additional_price, 0, ',', '.') : '0' }} đ
                            </td>
                            <td class="text-end">{{ $v->stock_quantity }}</td>
                            <td>
                                @if($v->status)
                                <span class="badge text-bg-success">Active</span>
                                @else
                                <span class="badge text-bg-secondary">Ẩn</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.variants.show', $v) }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                                <button type="button" class="btn btn-light btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editVariantModal"
                                    data-id="{{ $v->id }}"
                                    data-color="{{ $v->color }}"
                                    data-storage="{{ $v->storage }}"
                                    data-stock="{{ $v->stock_quantity }}"
                                    data-price="{{ $v->additional_price }}"
                                    data-status="{{ $v->status }}">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Chưa có biến thể nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Cột phải: ảnh --}}
    <div class="col-lg-4">
        <section class="panel mb-3">
            <div class="panel-header">
                <h5 class="mb-0">Ảnh đại diện</h5>
            </div>
            <div class="p-3 text-center">
                @if($product->thumbnail)
                <img src="{{ Storage::url($product->thumbnail) }}" alt="{{ $product->name }}"
                    class="img-fluid rounded border" style="max-height:260px;object-fit:cover;">
                @else
                <div class="text-muted py-4">Chưa có ảnh đại diện</div>
                @endif
            </div>
        </section>

        @if($product->images->count())
        <section class="panel">
            <div class="panel-header">
                <h5 class="mb-0">Ảnh bổ sung</h5>
            </div>
            <div class="p-3 d-flex flex-wrap gap-2">
                @foreach($product->images as $img)
                <img src="{{ Storage::url($img->image_path) }}" width="80" height="80"
                    class="rounded border" style="object-fit:cover;">
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>

{{-- Modal sửa biến thể --}}
<div class="modal fade" id="editVariantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editVariantForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Sửa biến thể</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Màu <span class="text-danger">*</span></label>
                            <input type="text" name="color" id="vColor" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bộ nhớ <span class="text-danger">*</span></label>
                            <input type="text" name="storage" id="vStorage" class="form-control" required>
                        </div>
                        <div class="col-md-6" id="vStockWrapper">
                            <label class="form-label" id="vStockLabel">Tồn kho</label>
                            <input type="number" name="stock_quantity" id="vStock" class="form-control" min="0">
                        </div>
                        <div class="col-12 d-none" id="vImeiWrapper">
                            <label class="form-label">Danh sách IMEI / Serial mới <span class="text-danger">*</span></label>
                            <textarea name="imeis" id="vImeis" rows="5" class="form-control"
                                placeholder="Mỗi dòng một IMEI hoặc Serial&#10;123456789012345&#10;123456789012346&#10;123456789012347"></textarea>
                            <div class="form-text">Nhập IMEI mới muốn bổ sung. IMEI đã tồn tại sẽ được bỏ qua.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá thêm (đ)</label>
                            <input type="number" name="additional_price" id="vPrice" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" id="vStatus" value="1">
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

@push('scripts')
<script>
const productType = '{{ $product->product_type }}';

document.getElementById('editVariantModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id      = btn.dataset.id;
    const color   = btn.dataset.color;
    const storage = btn.dataset.storage;
    const stock   = btn.dataset.stock;
    const price   = btn.dataset.price;
    const status  = btn.dataset.status;

    document.getElementById('vColor').value   = color;
    document.getElementById('vStorage').value = storage;
    document.getElementById('vStock').value   = stock;
    document.getElementById('vPrice').value   = price;
    document.getElementById('vStatus').checked = status == '1';

    // Label tồn kho theo loại sản phẩm
    if (productType === 'imei/serial') {
        document.getElementById('vStockWrapper').classList.add('d-none');
        document.getElementById('vImeiWrapper').classList.remove('d-none');
    } else {
        document.getElementById('vStockWrapper').classList.remove('d-none');
        document.getElementById('vImeiWrapper').classList.add('d-none');
        document.getElementById('vStockLabel').textContent = 'Tồn kho';
    }

    document.getElementById('editVariantForm').action =
        `{{ url('admin/variants') }}/${id}`;
});
</script>
@endpush
