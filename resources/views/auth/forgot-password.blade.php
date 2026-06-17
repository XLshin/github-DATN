<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 500px;
            background: #fff;
            border-radius: 22px;
            padding: 36px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.35);
        }
        h1 { margin: 0 0 10px; color: #0f172a; }
        p { margin: 0 0 24px; color: #64748b; line-height: 1.6; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #334155;
        }
        input {
            width: 100%;
            height: 46px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 15px;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }
        .error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 13px;
        }
        .hint {
            margin-top: 6px;
            color: #64748b;
            font-size: 13px;
        }
        .btn {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            background: #2563eb;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 22px;
            color: #64748b;
        }
        .footer a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }
        .captcha-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Quên mật khẩu</h1>
        <p>Nhập email hoặc số điện thoại đã đăng ký. Nếu tài khoản tồn tại, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.</p>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label for="identifier">Email hoặc số điện thoại</label>
            <input
                id="identifier"
                type="text"
                name="identifier"
                value="{{ old('identifier') }}"
                placeholder="example@email.com hoặc 0912345678"
                required
                autocomplete="username"
            >
            <div class="hint">Chúng tôi không tiết lộ tài khoản có tồn tại hay không.</div>

            @error('identifier')
                <div class="error">{{ $message }}</div>
            @enderror

            @if (!empty($captcha['required']))
                <div class="captcha-box">
                    <label for="captcha">Xác minh bảo mật: {{ $captcha['question'] }}</label>
                    <input id="captcha" type="text" name="captcha" inputmode="numeric" required>
                    @error('captcha')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <button type="submit" class="btn">Gửi hướng dẫn đặt lại mật khẩu</button>
        </form>

        <div class="footer">
            <a href="{{ route('login') }}">Quay lại đăng nhập</a>
        </div>
    </div>
</body>
</html>
