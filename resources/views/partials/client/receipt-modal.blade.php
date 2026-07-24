{{-- Hóa đơn điện tử — hiện ngay khi hệ thống phát hiện giao dịch đã được thanh toán, phỏng theo
    đúng màn hình "Giao dịch thành công" của từng app đang mô phỏng (MoMo hồng, VNPAY xanh dương,
    ngân hàng/thẻ kiểu TPBank/VCB Digibank nền tối) để trông giống thật thay vì một khuôn dùng chung. --}}
@php
    $businessName = config('services.sepay.account_name');
@endphp

<div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:340px">

        @if($method === 'momo')
        {{-- Khung MoMo: nền trắng, tròn hồng bo lên đầu card, số tiền hồng đậm --}}
        <div class="modal-content border-0 overflow-hidden" style="border-radius:20px">
            <div class="text-center pt-4 pb-3" style="background:#AE2070">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white"
                     style="width:60px;height:60px">
                    <i class="bi bi-check-lg" style="font-size:34px;color:#AE2070"></i>
                </div>
            </div>
            <div class="text-center px-4 pt-3 pb-2" style="margin-top:-1px">
                <div class="fw-bold" style="color:#AE2070">Giao dịch thành công!</div>
                <div class="fs-3 fw-bold mt-1" id="receipt-amount" style="color:#AE2070">—</div>
                <div class="small text-muted">Cảm ơn bạn đã thanh toán qua Ví MoMo</div>
            </div>
            <div class="px-4 pb-4">
                <div class="rounded-3 p-3 mt-2" style="background:#FFF0F5">
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Ví nhận tiền</span>
                        <span class="small fw-semibold text-end">{{ $businessName }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Người chuyển</span>
                        <span class="small fw-semibold text-end" id="receipt-payer-name">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Mã giao dịch</span>
                        <span class="small fw-semibold text-end" id="receipt-transaction-code">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Nội dung</span>
                        <span class="small fw-semibold text-end">{{ $code }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Thời gian</span>
                        <span class="small fw-semibold text-end" id="receipt-paid-at">—</span>
                    </div>
                </div>
                <a href="#" id="receipt-continue-btn" class="btn w-100 mt-3 fw-semibold text-white"
                   style="background:#AE2070;border-radius:24px">Về trang chủ</a>
            </div>
        </div>

        @elseif($method === 'vnpay')
        {{-- Khung VNPAY: nền trắng, tròn xanh dương, logo VNPAY --}}
        <div class="modal-content border-0 overflow-hidden" style="border-radius:20px">
            <div class="text-center pt-4 pb-2">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                     style="width:60px;height:60px;background:#EAF3FF">
                    <i class="bi bi-check-lg" style="font-size:34px;color:#005BAA"></i>
                </div>
                <div class="fw-bold" style="color:#005BAA">Giao dịch thành công!</div>
                <div class="fs-3 fw-bold mt-1" id="receipt-amount" style="color:#005BAA">—</div>
                <span class="badge mt-1" style="background:#005BAA;font-size:10px">VNPAY</span>
            </div>
            <div class="px-4 pb-4 pt-2">
                <div class="rounded-3 p-3 border" style="background:#F7FAFF">
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Đơn vị thụ hưởng</span>
                        <span class="small fw-semibold text-end">{{ $businessName }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Người chuyển</span>
                        <span class="small fw-semibold text-end" id="receipt-payer-name">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Mã giao dịch</span>
                        <span class="small fw-semibold text-end" id="receipt-transaction-code">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Nội dung</span>
                        <span class="small fw-semibold text-end">{{ $code }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="small text-muted">Thời gian</span>
                        <span class="small fw-semibold text-end" id="receipt-paid-at">—</span>
                    </div>
                </div>
                <a href="#" id="receipt-continue-btn" class="btn w-100 mt-3 fw-semibold text-white"
                   style="background:#005BAA;border-radius:8px">Tiếp tục</a>
            </div>
        </div>

        @else
        {{-- Khung ngân hàng/thẻ: nền tối kiểu TPBank/VCB Digibank --}}
        @php
            $brandColor = $method === 'card' ? '#16213e' : '#007A4D';
        @endphp
        <div class="modal-content border-0 overflow-hidden" style="border-radius:20px;background:#15151f;color:#fff">
            <div class="text-center pt-4 pb-3" style="background:{{ $brandColor }}">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white mb-2"
                     style="width:56px;height:56px">
                    <i class="bi bi-check-lg" style="font-size:32px;color:{{ $brandColor }}"></i>
                </div>
                <div class="fw-semibold">Giao dịch thành công!</div>
                <div class="fs-3 fw-bold mt-1" id="receipt-amount">—</div>
            </div>
            <div class="p-4">
                <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#2a2a3a!important">
                    <span class="small" style="color:#9a9ab0">Tên người thụ hưởng</span>
                    <span class="small fw-semibold text-end">{{ $businessName }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#2a2a3a!important">
                    <span class="small" style="color:#9a9ab0">Tài khoản thụ hưởng</span>
                    <span class="small fw-semibold text-end" id="receipt-business-account">—</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#2a2a3a!important">
                    <span class="small" style="color:#9a9ab0">Người chuyển</span>
                    <span class="small fw-semibold text-end" id="receipt-payer-name">—</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#2a2a3a!important">
                    <span class="small" style="color:#9a9ab0">Mã giao dịch</span>
                    <span class="small fw-semibold text-end" id="receipt-transaction-code">—</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#2a2a3a!important">
                    <span class="small" style="color:#9a9ab0">Nội dung</span>
                    <span class="small fw-semibold text-end">{{ $code }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="small" style="color:#9a9ab0">Thời gian</span>
                    <span class="small fw-semibold text-end" id="receipt-paid-at">—</span>
                </div>

                <a href="#" id="receipt-continue-btn" class="btn w-100 mt-3 fw-semibold"
                   style="background:{{ $brandColor }};color:#fff">Tiếp tục</a>
            </div>
        </div>
        @endif

    </div>
</div>
