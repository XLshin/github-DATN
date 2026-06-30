@extends('layouts.client')

@section('title', 'Lịch sử điểm tích lũy')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">Lịch sử điểm tích lũy</h1>

            {{-- Current Points --}}
            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Điểm hiện tại</h6>
                            <p class="display-6 fw-bold text-success mt-2">{{ number_format(auth()->user()->points) }}</p>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem;">
                            <i class="bi bi-star-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- History Table --}}
            @if ($histories->isEmpty())
                <div class="card border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem;" class="text-muted"></i>
                        <p class="text-muted mt-3">Chưa có lịch sử điểm tích lũy nào.</p>
                    </div>
                </div>
            @else
                <div class="card border-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Loại</th>
                                    <th>Mô tả</th>
                                    <th class="text-end">Điểm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($histories as $history)
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
                                    <td class="small">{{ $history->description ?? '—' }}</td>
                                    <td class="text-end fw-semibold">
                                        @if($history->points > 0)
                                            <span class="text-success">+{{ number_format($history->points) }}</span>
                                        @else
                                            <span class="text-danger">{{ number_format($history->points) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 sticky-top" style="top: 2rem;">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">Menu</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag"></i> Đơn hàng của tôi
                        </a>
                        <a href="{{ route('points.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-star-fill"></i> Điểm tích lũy
                        </a>
                        <a href="{{ route('points.history') }}" class="list-group-item list-group-item-action active">
                            <i class="bi bi-clock-history"></i> Lịch sử điểm
                        </a>
                        <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-person"></i> Hồ sơ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
