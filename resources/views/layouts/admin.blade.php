<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="H-Phone Store - Quản trị hệ thống">
    <title>@yield('title', 'Quản trị') — Byte Zone Admin</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" data-turbo-track="reload">
    @stack('styles')

    <script>
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>

    <script type="module" src="https://unpkg.com/@hotwired/turbo"></script>
    <style>
        .media-box {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-content {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
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