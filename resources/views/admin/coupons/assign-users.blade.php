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
            {{-- Thông tin Voucher --}}
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

            {{-- Chọn khách hàng --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white fw-bold">
                    Chọn khách hàng
                </div>
                <div class="card-body">
                    {{-- Ô TÌM KIẾM MỚI ĐƯỢC THÊM VÀO --}}
                    <div class="mb-3 position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchCustomer" class="form-control border-start-0 ps-0"
                                   placeholder="Nhập tên hoặc email để tìm kiếm khách hàng nhanh...">
                        </div>
                        <div id="noResultMsg" class="text-muted small mt-2 d-none">Không tìm thấy khách hàng phù hợp.</div>
                    </div>
                    {{-- --------------------------- --}}

                    <form method="POST" action="{{ route('admin.coupons.assign-users-update', $coupon) }}">
                        @csrf
                        @method('PATCH')

                        <div class="list-group" id="customerList">
                            @forelse($allUsers as $user)
                                {{-- Thêm class user-item và data attributes để phục vụ việc tìm kiếm qua JS --}}
                                <label class="list-group-item d-flex align-items-center user-item"
                                       data-name="{{ Str::lower($user->name) }}"
                                       data-email="{{ Str::lower($user->email) }}">
                                    <input class="form-check-input me-2" type="checkbox" name="user_ids[]"
                                        value="{{ $user->id }}"
                                        {{ $coupon->users->contains($user->id) ? 'checked' : '' }}>
                                    <div class="ms-2">
                                        <strong class="customer-name">{{ $user->name }}</strong>
                                        <small class="text-muted d-block customer-email">{{ $user->email }}</small>
                                        <small class="{{ $user->orders_count === 0 ? 'text-danger' : 'text-success' }} d-block">
                                            <i class="bi {{ $user->orders_count === 0 ? 'bi-exclamation-triangle' : 'bi-bag-check' }}"></i>
                                            Tổng số đơn hàng: {{ number_format($user->orders_count) }}
                                            @if ($user->orders_count === 0)
                                                (chưa từng đặt đơn)
                                            @endif
                                        </small>
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
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT XỬ LÝ TÌM KIẾM TẠI CHỖ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchCustomer');
            const userItems = document.querySelectorAll('.user-item');
            const noResultMsg = document.getElementById('noResultMsg');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    // Chuyển từ khóa tìm kiếm sang tiếng Việt không dấu (hoặc chữ thường) để tìm chính xác hơn
                    const keyword = this.value.toLowerCase().trim();
                    let hasResults = false;

                    userItems.forEach(function (item) {
                        const name = item.getAttribute('data-name');
                        const email = item.getAttribute('data-email');

                        // Kiểm tra nếu tên hoặc email chứa từ khóa
                        if (name.includes(keyword) || email.includes(keyword)) {
                            item.classList.remove('d-none');
                            item.classList.add('d-flex'); // Giữ lại cấu trúc flexban đầu của Bootstrap
                            hasResults = true;
                        } else {
                            item.classList.remove('d-flex');
                            item.classList.add('d-none');
                        }
                    });

                    // Hiển thị thông báo nếu không tìm thấy ai
                    if (!hasResults && keyword !== '') {
                        noResultMsg.classList.remove('d-none');
                    } else {
                        noResultMsg.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endsection
