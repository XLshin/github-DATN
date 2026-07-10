<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cửa hàng điện thoại') — Byte Zone Store</title>
    <meta name="description" content="@yield('description', 'Byte Zone Store - Cửa hàng điện thoại, phụ kiện chính hãng, giá tốt.')">

    @include('partials.client.styles')
    @stack('styles')

    <script type="module" src="https://unpkg.com/@hotwired/turbo"></script>

    <style>
        .turbo-progress-bar {
            height: 3px;
            background-color: #0d6efd;
            /* Đổi mã màu này theo theme thực tế của cửa hàng */
        }
    </style>
</head>

<body class="client-body d-flex flex-column min-vh-100">
    @include('partials.client.preloader')

    @include('partials.client.navbar')

    @yield('header')

    <main class="flex-grow-1">
        <div class="container">
            @include('partials.flash-messages')
        </div>

        @yield('content')
    </main>

    @include('partials.client.footer')

    @auth
        @if (auth()->user()->role === 'customer')
            @include('partials.client.assistant-widget')
        @endif
    @endauth

    @include('partials.client.scripts')
    @stack('scripts')
</body>

</html>
