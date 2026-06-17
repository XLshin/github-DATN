<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f1f5f9;
            color: #0f172a;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #ffffff;
            padding: 32px;
        }

        .card-header h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .card-header p {
            margin: 0;
            color: #dbeafe;
        }

        .card-body {
            padding: 32px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .info-item {
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
        }

        .info-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 16px;
            color: #0f172a;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 13px;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-danger {
            background: #dc2626;
            color: #ffffff;
        }

        @media (max-width: 700px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .card-header,
            .card-body {
                padding: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Thông tin cá nhân</h1>
                <p>Xem thông tin tài khoản hiện tại của bạn.</p>
            </div>

            <div class="card-body">
                @if (session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
                @endif

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Họ và tên</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Số điện thoại</div>
                        <div class="info-value">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Vai trò</div>
                        <div class="info-value">
                            <span class="badge">{{ $user->role }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Hạng thành viên</div>
                        <div class="info-value">{{ $user->membership_level ?? 'bronze' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tổng chi tiêu</div>
                        <div class="info-value">{{ number_format($user->total_spent ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>

                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Địa chỉ</div>
                        <div class="info-value">{{ $user->address ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        Cập nhật thông tin
                    </a>

                    <a href="{{ route('password.change') }}" class="btn btn-secondary">
                        Đổi mật khẩu
                    </a>

                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        Về dashboard
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>