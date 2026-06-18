<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
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
        .form-group { margin-bottom: 18px; }
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
            margin-top: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 22px;
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
        }
        ul.requirements {
            margin: 8px 0 0;
            padding-left: 18px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Đặt lại mật khẩu</h1>
        <p>Nhập mật khẩu mới cho tài khoản của bạn.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <ul class="requirements">
                    <li>Ít nhất 8 ký tự</li>
                    <li>Có chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                </ul>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            @if (!empty($captcha['required']))
                <div class="form-group captcha-box">
                    <label for="captcha">Xác minh bảo mật: {{ $captcha['question'] }}</label>
                    <input id="captcha" type="text" name="captcha" inputmode="numeric" required>
                    @error('captcha')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            @error('token')
                <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn">Đặt lại mật khẩu</button>
        </form>

        <div class="footer">
            <a href="{{ route('login') }}">Quay lại đăng nhập</a>
        </div>
    </div>
</body>
</html>
