<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f1f5f9;
            color: #0f172a;
            padding: 40px 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            padding: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .card-header h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .card-header p {
            margin: 0;
            color: #dbeafe;
        }

        .card-body {
            padding: 32px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex: 1;
            min-width: 260px;
        }

        .form-control {
            height: 44px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 14px;
            outline: none;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .search-form .form-control {
            flex: 1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-warning {
            background: #f59e0b;
            color: #fff;
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .btn-sm {
            height: 36px;
            padding: 0 12px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 12px;
        }

        .badge-admin {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-customer {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }

        @media (max-width: 768px) {

            .card-header,
            .card-body {
                padding: 24px;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div>
                    <h1>Quản lý người dùng</h1>
                    <p>Danh sách tài khoản trong hệ thống (US08)</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Về dashboard</a>
            </div>

            <div class="card-body">
                @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                <div class="alert-error">{{ session('error') }}</div>
                @endif

                <div class="toolbar">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Tìm theo tên, email, số điện thoại...">
                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                    </form>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Thêm người dùng</a>
                </div>

                @if ($users->isEmpty())
                <div class="empty-state">Không tìm thấy người dùng nào.</div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Vai trò</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-customer' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="actions-cell">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">Sửa</a>
                                    @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="pagination">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>