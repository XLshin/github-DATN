@extends('layouts.app')

@section('title', 'Điểm tích lũy của tôi')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">Điểm tích lũy của tôi</h1>

            {{-- Points Summary --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted mb-0">Điểm hiện tại</h6>
                                    <p class="display-5 fw-bold text-success mt-2">{{ number_format(auth()->user()->points) }}</p>
                                </div>
                                <div class="text-success" style="font-size: 3rem;">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-muted mb-0">Quy đổi</h6>
                                    <p class="display-6 fw-bold text-primary mt-2">{{ number_format(auth()->user()->points) }} đ</p>
                                    <small class="text-muted">1 điểm = 1 đ</small>
                                </div>
                                <div class="text-primary" style="font-size: 3rem;">
                                    <i class="bi bi-currency-exchange"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- How to earn points --}}
            <div class="card border-0 mb-4">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Cách kiếm điểm
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            <strong>Mỗi lần mua hàng:</strong> Bạn sẽ nhận 1 điểm cho mỗi 1.000 đ mua hàng (0,1%)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            <strong>Thưởng từ admin:</strong> Có thể nhận điểm thưởng từ quản trị viên
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success"></i>
                            <strong>Không có hạn sử dụng:</strong> Điểm của bạn sẽ luôn được lưu lại
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Point History Link --}}
            <div class="card border-0">
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Xem chi tiết lịch sử tích lũy điểm của bạn</p>
                    <a href="{{ route('points.history') }}" class="btn btn-primary">
                        <i class="bi bi-clock-history"></i> Lịch sử điểm
                    </a>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            @include('client.profile.partials.account-menu')
        </div>
    </div>
</div>
@endsection
