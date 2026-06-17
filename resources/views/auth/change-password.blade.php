<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi mật khẩu</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            padding: 28px;
        }
        .card-header h1 { margin: 0 0 8px; font-size: 28px; }
        .card-header p { margin: 0; color: #dbeafe; }
        .card-body { padding: 28px; }
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
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }
        .btn {
            border: none;
            border-radius: 12px;
            height: 46px;
            padding: 0 18px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1>Đổi mật khẩu</h1>
            <p>Cập nhật mật khẩu để bảo vệ tài khoản của bạn.</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input id="current_password" type="password" name="current_password" required>
                    @error('current_password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu mới</label>
                    <input id="password" type="password" name="password" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Lưu mật khẩu</button>
                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>