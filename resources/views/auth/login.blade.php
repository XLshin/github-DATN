<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a8a, #2563eb, #0f172a);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 980px;
            min-height: 560px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.35);
        }

        .auth-banner {
            background: linear-gradient(160deg, #0f172a, #1d4ed8);
            color: #ffffff;
            padding: 56px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-banner h1 {
            font-size: 38px;
            line-height: 1.2;
            margin: 0 0 18px;
        }

        .auth-banner p {
            font-size: 16px;
            line-height: 1.7;
            color: #dbeafe;
            margin: 0;
        }

        .auth-card {
            padding: 56px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-card h2 {
            margin: 0 0 8px;
            font-size: 30px;
            color: #0f172a;
        }

        .auth-card .subtitle {
            margin: 0 0 28px;
            color: #64748b;
            font-size: 15px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            height: 46px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .error-message {
            margin-top: 6px;
            color: #dc2626;
            font-size: 13px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0 22px;
            color: #475569;
            font-size: 14px;
        }

        .btn-primary {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .auth-footer {
            margin-top: 24px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .auth-footer a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
            }

            .auth-banner {
                display: none;
            }

            .auth-card {
                padding: 38px 24px;
            }
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-banner">
            <h1>Chào mừng quay lại</h1>
            <p>
                Đăng nhập để tiếp tục quản lý tài khoản, theo dõi thông tin cá nhân
                và sử dụng các chức năng trong hệ thống.
            </p>
        </div>

        <div class="auth-card">
            <h2>Đăng nhập</h2>
            <p class="subtitle">Vui lòng nhập thông tin tài khoản của bạn.</p>

            @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Nhập email của bạn"
                        required
                        autofocus>

                    @error('email')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Nhập mật khẩu"
                        required>

                    @error('password')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Ghi nhớ đăng nhập</span>
                </label>

                <div style="text-align: right; margin-bottom: 18px;">
                    <a href="{{ route('password.request') }}" style="color: #2563eb; font-size: 14px; font-weight: 600; text-decoration: none;">
                        Quên mật khẩu?
                    </a>
                </div>

                <button type="submit" class="btn-primary">
                    Đăng nhập
                </button>
            </form>

            <div class="auth-footer">
                Chưa có tài khoản?
                <a href="{{ route('register') }}">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</body>

</html>