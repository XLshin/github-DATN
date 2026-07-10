@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')
@section('page_icon', 'bi-person-lines-fill')
@section('page_eyebrow', 'Quản lý khách hàng')
@section('page_title', 'Chi tiết khách hàng')
@section('page_subtitle', 'Xem thông tin tài khoản, lịch sử mua hàng và bảo hành.')

@section('heading_actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
</a>
@endsection

@section('content')
{{-- Thông tin cơ bản --}}
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
            {{-- Các ô thông tin giữ nguyên --}}
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
                    <div class="fw-semibold">{{ number_format($user->total_spent ?? 0, 0, ',', '.') }} đ</div>
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
                    <span class="badge text-bg-info">{{ ucfirst($user->membership_level ?? 'bronze') }}</span>
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

{{-- Tab Đơn hàng & Bảo hành --}}
@if ($user->isCustomer())
<ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
            <i class="bi bi-bag"></i> Đơn hàng ({{ $orders->total() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="warranties-tab" data-bs-toggle="tab" data-bs-target="#warranties" type="button" role="tab">
            <i class="bi bi-shield-check"></i> Bảo hành ({{ $warranties->total() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="userTabsContent">
    {{-- Tab Đơn hàng --}}
    <div class="tab-pane fade show active" id="orders" role="tabpanel">
        <section class="panel">
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
                                'pending' => ['label' => 'Chờ xác nhận','class' => 'text-bg-warning'],
                                'processing' => ['label' => 'Đang xử lý','class' => 'text-bg-primary'],
                                'shipping' => ['label' => 'Đang giao','class' => 'text-bg-primary'],
                                'completed' => ['label' => 'Hoàn thành','class' => 'text-bg-success'],
                                'cancelled' => ['label' => 'Đã hủy','class' => 'text-bg-danger'],
                                'failed' => ['label' => 'Giao thất bại','class' => 'text-bg-danger'],
                                ];
                                $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'text-bg-secondary'];
                                @endphp
                                <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </td>
                            <td>
                                @php
                                $paymentStatus = $order->payment_status ?? null;
                                $paymentLabel = 'Chưa cập nhật';
                                $paymentClass = 'text-bg-light';
                                if (in_array($paymentStatus, ['paid','completed'])) {
                                $paymentLabel = 'Đã thanh toán';
                                $paymentClass = 'text-bg-success';
                                } elseif (in_array($paymentStatus, ['pending','unpaid'])) {
                                $paymentLabel = 'Chưa thanh toán';
                                $paymentClass = 'text-bg-warning';
                                } elseif ($order->status === 'completed') {
                                $paymentLabel = 'Đã thanh toán';
                                $paymentClass = 'text-bg-success';
                                }
                                @endphp
                                <span class="badge {{ $paymentClass }}">{{ $paymentLabel }}</span>
                            </td>
                            <td>{{ number_format($order->total_amount ?? ($order->total ?? 0), 0, ',', '.') }} đ</td>
                            <td>
                                @if ($order->items->count())
                                <ul class="mb-0 ps-3">
                                    @foreach ($order->items as $item)
                                    <li>{{ $item->product->name ?? 'Sản phẩm' }} x{{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Không có đơn hàng nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
            <div class="p-3">
                {{ $orders->appends(['warrantiesPage' => request('warrantiesPage')])->links() }}
            </div>
            @endif
        </section>
    </div>

    {{-- Tab Bảo hành --}}
    <div class="tab-pane fade" id="warranties" role="tabpanel">
        <section class="panel">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IMEI</th>
                            <th>Đơn hàng</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th class="text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warranties as $warranty)
                        <tr>
                            <td>#{{ $warranty->id }}</td>
                            <td><code>{{ $warranty->imei->imei ?? 'N/A' }}</code></td>
                            <td>
                                @if($warranty->order)
                                <a href="{{ route('admin.orders.show', $warranty->order) }}">#{{ $warranty->order->order_code ?? $warranty->order->id }}</a>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $warranty->created_at->format('d/m/Y') }}</td>
                            <td>
                                @php
                                $warrantyStatusClass = match($warranty->status) {
                                'claimed' => 'text-bg-warning',
                                'active' => 'text-bg-success',
                                'expired' => 'text-bg-secondary',
                                default => 'text-bg-light'
                                };
                                $warrantyStatusLabel = match($warranty->status) {
                                'claimed' => 'Đang xử lý',
                                'active' => 'Đã xong',
                                'expired' => 'Hết hạn',
                                default => $warranty->status
                                };
                                @endphp
                                <span class="badge {{ $warrantyStatusClass }}">{{ $warrantyStatusLabel }}</span>
                            </td>
                            <td>{{ Str::limit($warranty->customer_note, 50) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Không có phiếu bảo hành nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($warranties->hasPages())
            <div class="p-3">
                {{ $warranties->appends(['ordersPage' => request('ordersPage')])->links() }}
            </div>
            @endif
        </section>
    </div>
</div>
@else
<div class="alert alert-info">Tài khoản admin/nhân viên không có lịch sử mua hàng hay bảo hành.</div>
@endif
@endsection