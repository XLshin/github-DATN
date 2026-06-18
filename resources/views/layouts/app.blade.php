<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H-Phone Store - Hệ Thống Quản Trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; background-color: #f8f9fa; }
        .sidebar { width: 250px; background: #212529; color: white; padding: 20px; }
        .sidebar a { color: #adbcbc; text-decoration: none; display: block; padding: 10px; border-radius: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #495057; color: white; }
        .main-content { flex: 1; padding: 30px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4 class="text-center text-warning mb-4">H-Phone Admin</h4>
        <hr>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Tổng quan</a>
        <a href="{{ route('reviews.index') }}" class="{{ request()->routeIs('reviews.index') ? 'active' : '' }}">💬 Quản lý Đánh giá</a>
        <a href="#" class="mt-5 text-danger">👋 Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
