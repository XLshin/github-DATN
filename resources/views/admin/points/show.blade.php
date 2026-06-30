@extends('layouts.admin')

@section('title', 'Quản lý điểm - ' . $user->name)
@section('page_icon', 'bi-star')
@section('page_eyebrow', 'Quản lý thành viên')
@section('page_title', 'Chi tiết điểm: ' . $user->name)
@section('page_subtitle', 'Xem lịch sử và quản lý điểm tích lũy của khách hàng.')

@section('content')
    <div class="row">
        {{-- User Info Card --}}
        <div class="col-lg-4">
            <section class="panel">
                <header class="panel-header">
                    <h3 class="panel-title">Thông tin khách hàng</h3>
                </header>
                <div class="panel-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Tên khách hàng</label>
                        <p class="fw-semibold">{{ $user->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <p>{{ $user->email }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Điện thoại</label>
                        <p>{{ $user->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Tổng tiền đã chi</label>
                        <p class="fw-semibold text-danger">{{ number_format($user->total_spent ?? 0, 0, ',', '.') }} đ</p>
                    </div>
                    <hr />
                    <div class="text-center">
                        <label class="form-label text-muted small">Điểm tích lũy hiện tại</label>
                        <p class="display-5 fw-bold text-success">{{ number_format($user->points) }}</p>
                    </div>
                </div>
            </section>
        </div>

        {{-- Add/Deduct Points Form --}}
        <div class="col-lg-8">
            {{-- Add Points --}}
            <section class="panel mb-3">
                <header class="panel-header">
                    <h3 class="panel-title">
                        <i class="bi bi-plus-circle text-success"></i> Cộng điểm
                    </h3>
                </header>
                <div class="panel-body">
                    <form action="{{ route('admin.points.add', $user) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="add_points" class="form-label">Số điểm cộng thêm</label>
                            <input
                                type="number"
                                id="add_points"
                                name="points"
                                class="form-control @error('points') is-invalid @enderror"
                                placeholder="Nhập số điểm muốn cộng"
                                min="1"
                                max="1000000"
                                required
                            />
                            @error('points')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="add_description" class="form-label">Lý do (tùy chọn)</label>
                            <input
                                type="text"
                                id="add_description"
                                name="description"
                                class="form-control"
                                placeholder="Nhập lý do cộng điểm"
                                maxlength="255"
                            />
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Cộng điểm
                        </button>
                    </form>
                </div>
            </section>

            {{-- Deduct Points --}}
            <section class="panel mb-3">
                <header class="panel-header">
                    <h3 class="panel-title">
                        <i class="bi bi-dash-circle text-warning"></i> Trừ điểm
                    </h3>
                </header>
                <div class="panel-body">
                    <form action="{{ route('admin.points.deduct', $user) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="deduct_points" class="form-label">Số điểm trừ đi</label>
                            <input
                                type="number"
                                id="deduct_points"
                                name="points"
                                class="form-control @error('points') is-invalid @enderror"
                                placeholder="Nhập số điểm muốn trừ"
                                min="1"
                                max="1000000"
                                required
                            />
                            @error('points')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deduct_description" class="form-label">Lý do (tùy chọn)</label>
                            <input
                                type="text"
                                id="deduct_description"
                                name="description"
                                class="form-control"
                                placeholder="Nhập lý do trừ điểm"
                                maxlength="255"
                            />
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-check-circle"></i> Trừ điểm
                        </button>
                    </form>
                </div>
            </section>

            {{-- Reset Points --}}
            <section class="panel">
                <header class="panel-header">
                    <h3 class="panel-title">
                        <i class="bi bi-arrow-repeat text-danger"></i> Reset điểm
                    </h3>
                </header>
                <div class="panel-body">
                    <form action="{{ route('admin.points.reset', $user) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn reset tất cả {{ number_format($user->points) }} điểm của {{ $user->name }}?')">
                        @csrf
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Cảnh báo!</strong> Hành động này sẽ xóa tất cả {{ number_format($user->points) }} điểm của khách hàng và không thể hoàn tác.
                        </div>
                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                id="confirmation"
                                name="confirmation"
                                class="form-check-input @error('confirmation') is-invalid @enderror"
                                value="on"
                                required
                            />
                            <label class="form-check-label" for="confirmation">
                                Tôi xác nhận muốn reset tất cả điểm
                            </label>
                            @error('confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-arrow-repeat"></i> Reset tất cả điểm
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    {{-- Point History --}}
    <section class="panel mt-4">
        <header class="panel-header">
            <h3 class="panel-title">Lịch sử điểm</h3>
        </header>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Loại</th>
                        <th>Điểm</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pointHistories as $history)
                    <tr>
                        <td class="small text-muted">
                            {{ $history->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @switch($history->type)
                                @case('purchase')
                                    <span class="badge text-bg-success">Mua hàng</span>
                                    @break
                                @case('admin_adjustment')
                                    <span class="badge text-bg-info">Admin điều chỉnh</span>
                                    @break
                                @case('admin_reset')
                                    <span class="badge text-bg-danger">Admin reset</span>
                                    @break
                                @default
                                    <span class="badge text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $history->type)) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($history->points > 0)
                                <span class="text-success fw-semibold">+{{ number_format($history->points) }}</span>
                            @else
                                <span class="text-danger fw-semibold">{{ number_format($history->points) }}</span>
                            @endif
                        </td>
                        <td class="small">{{ $history->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Chưa có lịch sử điểm nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($pointHistories, 'hasPages') && $pointHistories->hasPages())
        <div class="p-3">
            {{ $pointHistories->withQueryString()->links() }}
        </div>
        @endif
    </section>

    <div class="mt-4">
        <a href="{{ route('admin.points.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
@endsection
