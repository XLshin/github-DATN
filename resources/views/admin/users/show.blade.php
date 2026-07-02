@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')
@section('page_icon', 'bi-person-lines-fill')
@section('page_eyebrow', 'Quản lý khách hàng')
@section('page_title', 'Chi tiết khách hàng')
@section('page_subtitle', 'Xem thông tin tài khoản và lịch sử mua hàng của khách.')

@section('heading_actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
@endsection

@section('content')
    <section class="panel mb-4">
        <div class="panel-header">
            <div>
                <h5 class="mb-1">Thông tin cơ bản</h5>
                <div class="text-muted small">
                    Thông tin tài khoản khách hàng: <strong>{{ $user->name }}</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.toggle-lock', $user) }}"
                onsubmit="return confirm('{{ $user->is_locked ? 'Bạn có chắc muốn mở khóa tài khoản khách hàng này?' : 'Bạn có chắc muốn khóa tài khoản khách hàng này?' }}')">
                @csrf
                @method('PATCH')

                @if ($user->is_locked)
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-unlock"></i> Mở khóa tài khoản
                    </button>
                @else
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-lock"></i> Khóa tài khoản
                    </button>
                @endif
            </form>
        </div>

        <div class="p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Họ và tên</div>
                        <div class="fw-semibold">{{ $user->name }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Email</div>
                        <div class="fw-semibold">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Số điện thoại</div>
                        <div class="fw-semibold">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Vai trò</div>
                        <span class="badge text-bg-primary">Khách hàng</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Tổng chi tiêu</div>
                        <div class="fw-semibold">
                            {{ number_format($user->total_spent ?? 0, 0, ',', '.') }} đ
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Điểm tích lũy</div>
                        <div class="fw-semibold">{{ number_format($user->points ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Hạng thành viên</div>
                        <span class="badge text-bg-info">
                            {{ ucfirst($user->membership_level ?? 'bronze') }}
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Trạng thái tài khoản</div>

                        @if ($user->is_locked)
                            <span class="badge text-bg-danger">Đã khóa</span>
                        @else
                            <span class="badge text-bg-success">Đang hoạt động</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="border rounded p-3">
                        <div class="text-muted small mb-1">Địa chỉ</div>
                        <div class="fw-semibold">{{ $user->address ?? 'Chưa cập nhật' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Ngày tạo tài khoản</div>
                        <div class="fw-semibold">{{ $user->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-1">Cập nhật gần nhất</div>
                        <div class="fw-semibold">{{ $user->updated_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h5 class="mb-1">Lịch sử đặt hàng</h5>
                <div class="text-muted small">
                    Danh sách đơn hàng khách đã đặt/mua trong hệ thống.
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Sản phẩm</th>
                        <th class="text-end">Chi tiết</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>

                            <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>

                            <td>
                                @php
                                    $statusMap = [
                                        'pending' => [
                                            'label' => 'Chờ xác nhận',
                                            'class' => 'text-bg-warning',
                                        ],
                                        'confirmed' => [
                                            'label' => 'Đã xác nhận',
                                            'class' => 'text-bg-primary',
                                        ],
                                        'packed' => [
                                            'label' => 'Đã đóng gói',
                                            'class' => 'text-bg-info',
                                        ],
                                        'shipping' => [
                                            'label' => 'Đang giao hàng',
                                            'class' => 'text-bg-primary',
                                        ],
                                        'delivering' => [
                                            'label' => 'Đang giao hàng',
                                            'class' => 'text-bg-primary',
                                        ],
                                        'delivered' => [
                                            'label' => 'Đã giao hàng',
                                            'class' => 'text-bg-success',
                                        ],
                                        'completed' => [
                                            'label' => 'Hoàn thành',
                                            'class' => 'text-bg-success',
                                        ],
                                        'failed' => [
                                            'label' => 'Giao thất bại',
                                            'class' => 'text-bg-danger',
                                        ],
                                        'cancelled' => [
                                            'label' => 'Đã hủy',
                                            'class' => 'text-bg-danger',
                                        ],
                                        'canceled' => [
                                            'label' => 'Đã hủy',
                                            'class' => 'text-bg-danger',
                                        ],
                                    ];

                                    $status = $statusMap[$order->status] ?? [
                                        'label' => 'Chưa cập nhật',
                                        'class' => 'text-bg-secondary',
                                    ];
                                @endphp

                                <span class="badge {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $paymentStatus = $order->payment_status ?? null;

                                    $paymentLabel = 'Chưa cập nhật';
                                    $paymentClass = 'text-bg-light';

                                    if (in_array($paymentStatus, ['paid', 'success', 'completed', 'Đã thanh toán'])) {
                                        $paymentLabel = 'Đã thanh toán';
                                        $paymentClass = 'text-bg-success';
                                    } elseif (
                                        in_array($paymentStatus, ['unpaid', 'pending', 'waiting', 'Chưa thanh toán'])
                                    ) {
                                        $paymentLabel = 'Chưa thanh toán';
                                        $paymentClass = 'text-bg-warning';
                                    } elseif (
                                        in_array($paymentStatus, ['failed', 'cancelled', 'canceled', 'Thất bại'])
                                    ) {
                                        $paymentLabel = 'Thanh toán thất bại';
                                        $paymentClass = 'text-bg-danger';
                                    } elseif ($order->status === 'completed') {
                                        $paymentLabel = 'Đã thanh toán';
                                        $paymentClass = 'text-bg-success';
                                    }
                                @endphp

                                <span class="badge {{ $paymentClass }}">
                                    {{ $paymentLabel }}
                                </span>
                            </td>

                            <td>
                                {{ number_format($order->total_amount ?? ($order->total ?? ($order->grand_total ?? 0)), 0, ',', '.') }}
                                đ
                            </td>

                            <td>
                                @if ($order->items && $order->items->count())
                                    <ul class="mb-0 ps-3">
                                        @foreach ($order->items as $item)
                                            <li>
                                                {{ $item->product->name ?? ($item->product_name ?? 'Sản phẩm không tồn tại') }}
                                                x {{ $item->quantity ?? 1 }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">Không có dữ liệu sản phẩm</span>
                                @endif
                            </td>

                            <td class="text-end">
                                @if (Route::has('admin.orders.show'))
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-eye"></i> Xem đơn
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Khách hàng chưa có đơn hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-3">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
@endsection
