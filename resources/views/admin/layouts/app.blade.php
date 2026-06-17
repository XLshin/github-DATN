<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
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