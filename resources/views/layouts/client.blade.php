<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H-Phone Store - Điện thoại chính hãng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="/">H-PHONE STORE</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('points.index') }}">🎁 Điểm của tôi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('points.history') }}">📜 Lịch sử điểm</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('client.vouchers.index') }}">🏷️ Voucher của tôi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('warranties.lookup') }}">🛠️ Tra cứu bảo hành</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        @yield('content')
    </div>

    <footer class="bg-light text-center py-3 mt-5 border-top">
        <p class="mb-0 text-muted">&copy; 2026 H-Phone Store - Đồ án tốt nghiệp hệ thống thương mại điện tử.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
