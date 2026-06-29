<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Xác thực') — Byte Zone Store</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" data-turbo-track="reload">

    @stack('styles')

    <script>
        // Kiểm tra ngay trạng thái giao diện trong localStorage trước khi vẽ HTML
        const currentTheme = localStorage.getItem('theme'); // Đổi 'theme' thành tên key JS của bạn lưu

        if (currentTheme === 'dark') {
            // Tùy theo CSS của bạn, hãy mở comment dòng phù hợp bên dưới:
            document.documentElement.classList.add('dark');
            // HOẶC
            // document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>

    <script type="module" src="https://unpkg.com/@hotwired/turbo"></script>
    <style>
        .turbo-progress-bar {
            height: 3px;
            background-color: #0d6efd;
        }
    </style>
</head>

<body class="auth-body">
    <button class="icon-button theme-toggle auth-theme-toggle"
        type="button"
        data-theme-toggle
        aria-label="Switch color theme">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>

    <main class="auth-page">
        <section class="auth-card">
            <a class="auth-brand" href="{{ route('home') }}">
                <span class="brand-icon">
                    <i class="bi bi-phone" aria-hidden="true"></i>
                </span>

                <span>
                    <strong>Byte Zone Store</strong>
                    <small>@yield('auth_subtitle', 'Hệ thống quản lý tài khoản')</small>
                </span>
            </a>

            <div class="auth-heading">
                <h1>@yield('auth_heading', 'Chào mừng bạn')</h1>
                <p>@yield('auth_description', 'Đăng nhập hoặc tạo tài khoản để tiếp tục mua sắm tại Byte Zone Store.')</p>
            </div>

            @include('partials.flash-messages')

            <div class="auth-content">
                @yield('content')
            </div>

            @hasSection('auth_footer')
            <div class="auth-footer">
                @yield('auth_footer')
            </div>
            @endif
        </section>
    </main>

    @include('partials.admin.scripts')
    @stack('scripts')
</body>

</html>