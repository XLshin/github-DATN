<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="{{ route('admin.products.index') }}">
        <i class="bi bi-shop"></i> Admin DATN
    </a>
</nav>

<div class="container-fluid mt-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>

//của hiếu
</html>
    <title>Admin Test</title>
</head>
<body>
    <nav>
        <a href="{{ route('admin.shipments.index') }}">Vận chuyển</a> |
        <a href="{{ route('admin.shipments.lookup') }}">Tra cứu vận đơn</a> |
        <a href="{{ route('admin.warranties.index') }}">Bảo hành</a> |
        <a href="{{ route('admin.warranties.lookupImei') }}">Tra cứu IMEI</a>
    </nav>

    <hr>

    @yield('content')
</body>
</html>
