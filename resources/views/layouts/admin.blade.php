<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="H-Phone Store - Quản trị hệ thống">
    <title>@yield('title', 'Quản trị') — Byte Zone Admin</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>

        @include('partials.admin.sidebar')

        <div class="admin-main">
            @include('partials.admin.header')

            <main class="dashboard-content">
                <div class="container-fluid px-3 px-lg-4 py-4">
                    @include('partials.flash-messages')
                    @include('partials.admin.page-heading')
                    @yield('content')
                </div>
            </main>

            @include('partials.admin.footer')
        </div>
    </div>

    @include('partials.admin.scripts')
    @stack('scripts')
</body>
</html>
