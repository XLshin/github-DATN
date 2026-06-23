@extends('layouts.admin')

@section('title', $product->name)
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', $product->name)
@section('page_subtitle', 'Xem chi tiết sản phẩm, hình ảnh, trạng thái và các biến thể hiện có.')

@section('heading_actions')
<a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
    <i class="bi bi-pencil"></i> Sửa
</a>

<a href="{{ route('admin.products.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <section class="panel mb-3">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Thông tin sản phẩm</h5>
                    <div class="text-muted small">
                        Thông tin cơ bản, giá bán, tồn kho và trạng thái hiển thị.
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <tr>
                            <th style="width: 160px;">Danh mục</th>
                            <td>{{ $product->category->name ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Thương hiệu</th>
                            <td>{{ $product->brand->name ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Giá</th>
                            <td class="fw-semibold">
                                {{ number_format($product->price, 0, ',', '.') }} đ
                            </td>
                        </tr>

                        <tr>
                            <th>Tồn kho</th>
                            <td>{{ $product->stock_quantity }}</td>
                        </tr>

                        <tr>
                            <th>Slug</th>
                            <td>
                                <code>{{ $product->slug }}</code>
                            </td>
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
                            <td>
                                {{ $product->description }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>

        @if($product->images->count())
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Ảnh sản phẩm</h5>
                    <div class="text-muted small">
                        Danh sách ảnh bổ sung của sản phẩm.
                    </div>
                </div>
            </div>

            <div class="p-3 d-flex flex-wrap gap-2">
                @foreach($product->images as $img)
                <img
                    src="{{ Storage::url($img->image_path) }}"
                    alt="Ảnh sản phẩm"
                    width="100"
                    height="100"
                    class="rounded border"
                    style="object-fit: cover;">
                @endforeach
            </div>
        </section>
        @endif
    </div>

    <div class="col-lg-4">
        <section class="panel mb-3">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Ảnh đại diện</h5>
                    <div class="text-muted small">
                        Ảnh hiển thị chính của sản phẩm.
                    </div>
                </div>
            </div>

            <div class="p-3 text-center">
                @if($product->thumbnail)
                <img
                    src="{{ Storage::url($product->thumbnail) }}"
                    alt="{{ $product->name }}"
                    class="img-fluid rounded border"
                    style="max-height: 260px; object-fit: cover;">
                @else
                <div class="text-muted py-4">
                    Chưa có ảnh đại diện
                </div>
                @endif
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h5 class="mb-1">Biến thể</h5>
                    <div class="text-muted small">
                        Tổng số biến thể: {{ $product->variants->count() }}
                    </div>
                </div>
            </div>

            <div class="p-3">
                @forelse($product->variants as $v)
                <div class="border rounded p-2 mb-2 small">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge text-bg-secondary">
                                {{ $v->color }}
                            </span>

                            <span class="badge text-bg-info">
                                {{ $v->storage }}
                            </span>
                        </div>

                        @if($v->status)
                        <span class="badge text-bg-success">Active</span>
                        @else
                        <span class="badge text-bg-secondary">Off</span>
                        @endif
                    </div>

                    <div>
                        Tồn:
                        <strong>{{ $v->stock_quantity }}</strong>
                    </div>

                    <div>
                        Giá thêm:
                        <strong>{{ number_format($v->additional_price, 0, ',', '.') }} đ</strong>
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0">
                    Không có biến thể.
                </p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection