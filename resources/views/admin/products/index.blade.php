@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page_icon', 'bi-box-seam')
@section('page_eyebrow', 'Quản lý sản phẩm')
@section('page_title', 'Danh sách sản phẩm')
@section('page_subtitle', 'Quản lý sản phẩm, danh mục, thương hiệu, giá bán và tồn kho.')

@section('heading_actions')
<a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
</a>
@endsection

@section('content')
<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th class="text-end">Giá</th>
                    <th class="text-end">Tồn kho</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>

                    <td>
                        @if($product->thumbnail)
                        <img
                            src="{{ Storage::url($product->thumbnail) }}"
                            alt="{{ $product->name }}"
                            width="52"
                            height="52"
                            class="rounded"
                            style="object-fit: cover;">
                        @else
                        <span class="text-muted">Không có ảnh</span>
                        @endif
                    </td>

                    <td class="fw-semibold">
                        {{ $product->name }}
                    </td>

                    <td>
                        {{ $product->category->name ?? '-' }}
                    </td>

                    <td>
                        {{ $product->brand->name ?? '-' }}
                    </td>

                    <td class="text-end fw-semibold">
                        {{ number_format($product->price, 0, ',', '.') }} đ
                    </td>

                    <td class="text-end">
                        {{ $product->stock_quantity }}
                    </td>

                    <td>
                        @if($product->status)
                        <span class="badge text-bg-success">
                            Đang bán
                        </span>
                        @else
                        <span class="badge text-bg-secondary">
                            Ẩn
                        </span>
                        @endif
                    </td>

                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form
                                action="{{ route('admin.products.destroy', $product) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Xóa sản phẩm này?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Chưa có sản phẩm nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="p-3">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</section>
@endsection