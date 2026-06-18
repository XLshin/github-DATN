<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #2563eb, #1e40af);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1050px;
            min-height: 620px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.35);
        }

        .auth-banner {
            background: linear-gradient(160deg, #1e3a8a, #0f172a);
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
            padding: 44px;
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
            margin: 0 0 26px;
            color: #64748b;
            font-size: 15px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 17px;
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
            margin-top: 4px;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .auth-footer {
            margin-top: 22px;
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

        @media (max-width: 850px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
            }

            .auth-banner {
                display: none;
            }

            .auth-card {
                padding: 38px 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-banner">
            <h1>Tạo tài khoản mới</h1>
            <p>
                Đăng ký tài khoản để sử dụng hệ thống, quản lý thông tin cá nhân
                và trải nghiệm các chức năng dành cho người dùng.
            </p>
        </div>

        <div class="auth-card">
            <h2>Đăng ký</h2>
            <p class="subtitle">Điền đầy đủ thông tin để tạo tài khoản.</p>

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Nhập họ và tên"
                        required
                        autofocus>

                    @error('name')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Nhập email"
                            required>

                        @error('email')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="Nhập số điện thoại">

                        @error('phone')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Tối thiểu 8 ký tự"
                            required>

                        @error('password')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Xác nhận mật khẩu</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Nhập lại mật khẩu"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Tạo tài khoản
                </button>
            </form>

            <div class="auth-footer">
                Đã có tài khoản?
                <a href="{{ route('login') }}">Đăng nhập</a>
            </div>
        </div>
    </div>
</body>

</html>