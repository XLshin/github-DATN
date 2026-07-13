<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-2 text-muted small">Điểm hiện tại</div>
                <div class="display-6 fw-bold text-success">{{ number_format($user->points ?? 0) }}</div>
                <div class="text-muted small">{{ number_format($user->points ?? 0) }} đ</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-2 text-muted small">Voucher</div>
                <div class="display-6 fw-bold text-primary">{{ number_format($couponCount ?? 0) }}</div>
                <div class="text-muted small">Mã giảm giá còn hiệu lực</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-2 text-muted small">Bảo hành</div>
                <div class="display-6 fw-bold text-warning">{{ number_format($warrantyCount ?? 0) }}</div>
                <div class="text-muted small">Phiếu bảo hành liên quan</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center py-4">
                <div class="mb-2 text-muted small">Đơn hàng</div>
                <div class="display-6 fw-bold text-secondary">{{ number_format($user->orders()->count()) }}</div>
                <div class="text-muted small">Đơn hàng của bạn</div>
            </div>
        </div>
    </div>
</div>
