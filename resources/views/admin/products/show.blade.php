@extends('admin.layouts.app')

@section('title', $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-box-seam"></i> {{ $product->name }}</h4>
    <div>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Sửa
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th width="150">Danh mục</th><td>{{ $product->category->name ?? '-' }}</td></tr>
                    <tr><th>Thương hiệu</th><td>{{ $product->brand->name ?? '-' }}</td></tr>
                    <tr><th>Giá</th><td>{{ number_format($product->price) }}đ</td></tr>
                    <tr><th>Tồn kho</th><td>{{ $product->stock_quantity }}</td></tr>
                    <tr><th>Slug</th><td><code>{{ $product->slug }}</code></td></tr>
                    <tr><th>Trạng thái</th>
                        <td><span class="badge bg-{{ $product->status ? 'success' : 'secondary' }}">
                            {{ $product->status ? 'Đang bán' : 'Ẩn' }}
                        </span></td>
                    </tr>
                    <tr><th>Mô tả</th><td>{{ $product->description }}</td></tr>
                </table>
            </div>
        </div>

        @if($product->images->count())
        <div class="card shadow-sm">
            <div class="card-header"><strong>Ảnh sản phẩm</strong></div>
            <div class="card-body d-flex flex-wrap gap-2">
                @foreach($product->images as $img)
                    <img src="{{ Storage::url($img->image_path) }}" width="100" height="100" style="object-fit:cover" class="rounded border">
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        @if($product->thumbnail)
        <div class="card shadow-sm mb-3">
            <div class="card-body text-center">
                <img src="{{ Storage::url($product->thumbnail) }}" class="img-fluid rounded" style="max-height:250px">
            </div>
        </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header"><strong>Biến thể ({{ $product->variants->count() }})</strong></div>
            <div class="card-body p-2">
                @forelse($product->variants as $v)
                <div class="border rounded p-2 mb-1 small">
                    <span class="badge bg-secondary">{{ $v->color }}</span>
                    <span class="badge bg-info text-dark">{{ $v->storage }}</span>
                    <br>Tồn: <strong>{{ $v->stock_quantity }}</strong> | Giá thêm: <strong>{{ number_format($v->additional_price) }}đ</strong>
                    <span class="badge bg-{{ $v->status ? 'success' : 'secondary' }} float-end">{{ $v->status ? 'Active' : 'Off' }}</span>
                </div>
                @empty
                <p class="text-muted small mb-0">Không có biến thể.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
