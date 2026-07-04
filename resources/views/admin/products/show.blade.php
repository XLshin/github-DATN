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
                            <th class="text-end">Giá của biến thể</th>
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
                            <td class="text-end">
                                @if($product->product_type === 'imei/serial')
                                    @if($v->imeis->isNotEmpty())
                                        <div class="small text-monospace text-start">
                                            @foreach($v->imeis as $imei)
                                            <div>{{ $imei->imei }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                @else
                                    {{ $v->stock_quantity }}
                                @endif
                            </td>
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
                <div class="position-relative">
                    <img src="{{ Storage::url($img->image_path) }}" width="80" height="80"
                        class="rounded border" style="object-fit:cover;">
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>

@endsection