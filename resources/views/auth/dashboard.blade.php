<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>

<body>
    <h1>Xin chào, {{ auth()->user()->name }}</h1>

    <p>Email: {{ auth()->user()->email }}</p>
    <p>Vai trò: {{ auth()->user()->role }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Đăng xuất</button>
    </form>
</body>

</html>