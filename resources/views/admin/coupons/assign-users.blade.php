@extends('layouts.admin')

@section('title', 'Gán voucher cho khách hàng')
@section('page_icon', 'bi-ticket')
@section('page_eyebrow', 'Quản lý voucher')
@section('page_title', 'Gán voucher cho khách hàng')
@section('page_subtitle')
    Chọn những khách hàng được cấp voucher: {{ $coupon->code }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Thông tin voucher
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Mã voucher:</strong> {{ $coupon->code }}
                        </div>
                        <div class="col-md-6">
                            <strong>Loại giảm:</strong>
                            @if($coupon->discount_type === 'percent')
                                {{ $coupon->discount_value }}%
                            @else
                                {{ number_format($coupon->discount_value, 0, ',', '.') }} đ
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tối thiểu:</strong> {{ number_format($coupon->min_order_amount, 0, ',', '.') }} đ
                        </div>
                        <div class="col-md-6">
                            <strong>Hạn mức sử dụng:</strong> {{ $coupon->usage_limit === 0 ? 'Không giới hạn' : $coupon->usage_limit }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white fw-bold">
                    Chọn khách hàng
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('coupons.assign-users-update', $coupon) }}">
                        @csrf
                        @method('PATCH')

                        <div class="list-group">
                            @forelse($allUsers as $user)
                                <label class="list-group-item d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="user_ids[]"
                                        value="{{ $user->id }}"
                                        {{ $coupon->users->contains($user->id) ? 'checked' : '' }}>
                                    <div class="ms-2">
                                        <strong>{{ $user->name }}</strong>
                                        <small class="text-muted d-block">{{ $user->email }}</small>
                                    </div>
                                </label>
                            @empty
                                <p class="text-muted">Không có khách hàng nào.</p>
                            @endforelse
                        </div>

                        @error('user_ids')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
