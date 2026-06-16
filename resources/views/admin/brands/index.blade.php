<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách thương hiệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Danh sách thương hiệu</h2>
        <a href="{{ route('brands.create') }}" class="btn btn-primary">+ Thêm thương hiệu</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Logo</th>
                <th>Tên thương hiệu</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
            <tr>
                <td>{{ $brand->id }}</td>
                <td>
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}" width="60" alt="{{ $brand->name }}">
                    @else
                        <span class="text-muted">Không có</span>
                    @endif
                </td>
                <td>{{ $brand->name }}</td>
                <td>{{ $brand->description }}</td>
                <td>
                    <a href="{{ route('brands.edit', $brand) }}" class="btn btn-sm btn-warning">Sửa</a>
                    <form action="{{ route('brands.destroy', $brand) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Xóa thương hiệu này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">Chưa có thương hiệu nào</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $brands->links() }}
</div>
</body>
</html>
