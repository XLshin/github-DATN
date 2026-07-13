@extends('layouts.admin')

@section('title', $productGroup->name)
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', $productGroup->name)
@section('page_subtitle', 'Chi tiết sản phẩm, phiên bản, màu sắc, tồn kho và IMEI.')

@section('heading_actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
<a href="{{ route('admin.products.edit', $productGroup) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-pencil"></i> Sửa sản phẩm
</a>
@endsection

@push('styles')
<style>
    .product-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: .75rem;
    }

    .product-gallery img,
    .variant-image {
        width: 100%;
        object-fit: cover;
        border-radius: .65rem;
        border: 1px solid rgba(148, 163, 184, .28);
        background: #fff;
    }

    .product-gallery img {
        height: 120px;
    }

    .variant-image {
        width: 48px;
        height: 48px;
    }

    .spec-table th {
        color: #64748b;
        width: 180px;
    }

    .variant-price-form {
        display: grid;
        grid-template-columns: minmax(120px, 150px) auto;
        gap: .4rem;
        justify-content: end;
    }

</style>
@endpush

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <section class="panel h-100">
            <div class="panel-header">
                <h5 class="mb-0">Thông tin sản phẩm</h5>
            </div>
            <div class="p-3">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Danh mục</dt>
                    <dd class="col-7">{{ $productGroup->category->name ?? '-' }}</dd>

                    <dt class="col-5 text-muted">Thương hiệu</dt>
                    <dd class="col-7">{{ $productGroup->brand->name ?? '-' }}</dd>

                    <dt class="col-5 text-muted">Loại quản lý</dt>
                    <dd class="col-7">
                        @if($productGroup->product_type === 'imei/serial')
                        <span class="badge text-bg-warning text-dark">IMEI/Serial</span>
                        @else
                        <span class="badge text-bg-secondary">Theo số lượng</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Trạng thái</dt>
                    <dd class="col-7">
                        @if($productGroup->status)
                        <span class="badge text-bg-success">Hiển thị</span>
                        @else
                        <span class="badge text-bg-secondary">Ẩn</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted">Phiên bản</dt>
                    <dd class="col-7">{{ $productGroup->products->count() }}</dd>
                </dl>

                @if($productGroup->description)
                <hr>
                <div class="text-muted small mb-1">Mô tả</div>
                <div>{{ $productGroup->description }}</div>
                @endif
            </div>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="panel h-100">
            <div class="panel-header">
                <h5 class="mb-0">Ảnh sản phẩm</h5>
            </div>
            <div class="p-3">
                @if($productGroup->images->isNotEmpty())
                <div class="product-gallery">
                    @foreach($productGroup->images as $image)
                    <img src="{{ Storage::url($image->image_path) }}" alt="{{ $productGroup->name }}">
                    @endforeach
                </div>
                @else
                <div class="text-muted">Chưa có ảnh sản phẩm.</div>
                @endif
            </div>
        </section>
    </div>
</div>

<section class="panel mt-3">
    <div class="panel-header">
        <h5 class="mb-0">Thông số kỹ thuật</h5>
    </div>
    <div class="p-3">
        @if($productGroup->specifications->isNotEmpty())
        @foreach($productGroup->specifications->groupBy(fn ($spec) => $spec->group_name ?: 'Thông số') as $groupName => $specifications)
        <div class="fw-semibold mb-2">{{ $groupName }}</div>
        <div class="table-responsive mb-3">
            <table class="table table-sm spec-table mb-0">
                <tbody>
                    @foreach($specifications as $specification)
                    <tr>
                        <th>{{ $specification->name }}</th>
                        <td>{{ $specification->value }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        @else
        <div class="text-muted">Chưa có thông số kỹ thuật.</div>
        @endif
    </div>
</section>

<section class="panel mt-3">
    <div class="panel-header">
        <h5 class="mb-0">Phiên bản và màu sắc</h5>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Phiên bản</th>
                    <th>Màu</th>
                    <th>Ảnh</th>
                    <th class="text-end">Giá base</th>
                    <th class="text-end">Giá cộng màu</th>
                    <th class="text-end">Giá bán cuối</th>
                    <th class="text-end">Tồn kho</th>
                    <th class="text-end">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productGroup->products as $product)
                    @forelse($product->variants as $variant)
                    @php
                        $finalPrice = (float) $product->price + (float) $variant->additional_price;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <div class="text-muted small">{{ $product->storage ?: 'Không có dung lượng' }}</div>
                        </td>
                        <td>{{ $variant->color ?: 'Không màu' }}</td>
                        <td>
                            @if($variant->image_path)
                            <img src="{{ Storage::url($variant->image_path) }}" alt="{{ $variant->color }}" class="variant-image">
                            @else
                            <div class="variant-image d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-image"></i>
                            </div>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($product->price ?? 0, 0, ',', '.') }} đ</td>
                        <td class="text-end">
                            <form action="{{ route('admin.products.variants.price.update', $variant) }}" method="POST" class="variant-price-form">
                                @csrf
                                @method('PATCH')
                                <input type="number"
                                    name="additional_price"
                                    value="{{ old('additional_price', $variant->additional_price ?? 0) }}"
                                    min="0"
                                    class="form-control form-control-sm text-end">
                                <button type="submit" class="btn btn-light btn-sm" title="Lưu giá cộng thêm">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($finalPrice, 0, ',', '.') }} đ</td>
                        <td class="text-end">
                            @if($productGroup->product_type === 'imei/serial')
                                {{ $variant->available_imeis_count }}
                            @else
                                {{ $variant->stock_quantity }}
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.variants.show', $variant) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-muted">Phiên bản {{ $product->name }} chưa có màu.</td>
                    </tr>
                    @endforelse
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Sản phẩm chưa có phiên bản.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
