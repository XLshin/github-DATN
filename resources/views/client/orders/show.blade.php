@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng')

@section('header')
    <h1 class="h2 mb-1">Đơn hàng {{ $order->order_code }}</h1>
    <p class="text-muted mb-0">{{ $order->created_at->format('d/m/Y H:i') }}</p>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    @if($order->fulfillment_status === 'pending')
        <div class="mb-3 text-end">
            <button
                type="button"
                class="btn btn-outline-danger"
                data-bs-toggle="modal"
                data-bs-target="#cancelOrderModal">
                <i class="bi bi-x-circle me-1"></i>
                Hủy đơn hàng
            </button>
        </div>
    @endif
                    <h5 class="mb-3"><i class="bi bi-truck me-2"></i>Thông Tin Vận Chuyển</h5>
                    
                    @php
                        // Định nghĩa màu sắc khung theo trạng thái để tăng tính trực quan cho hộp thông báo hiện tại
                        $borderClass = match($order->fulfillment_status) {
                            'failed' => 'border-danger',
                            'shipping' => 'border-warning',
                            'completed' => 'border-success',
                            'cancelled' => 'border-danger',
                            default => 'border-primary',
                        };
                    @endphp

                    <div class="p-3 bg-light rounded border-start border-4 {{ $borderClass }} mb-4">
                        @switch($order->fulfillment_status)
                            @case('pending')
                                <h6 class="text-primary fw-bold mb-1">⏳ Đơn hàng đang chờ xác nhận</h6>
                                <p class="text-muted small mb-0">Hệ thống đã tiếp nhận đơn hàng của bạn. Nhân viên của ShopGrids sẽ kiểm tra và xác nhận đơn trong thời gian sớm nhất.</p>
                                @break

                            @case('waiting_pack')
                                <h6 class="text-info fw-bold mb-1">✅ Đã xác nhận đơn hàng</h6>
                                <p class="text-muted small mb-0">Đơn hàng của bạn đã được xác nhận lúc <strong>{{ $order->confirmed_at ? $order->confirmed_at->format('H:i d/m/Y') : '-' }}</strong>. Shop đang tiến hành chuẩn bị sản phẩm và đóng gói cẩn thận.</p>
                                @break

                            @case('waiting_handover')
                                <h6 class="text-primary fw-bold mb-1">📦 Đóng gói hoàn tất</h6>
                                <p class="text-muted small mb-0">Sản phẩm đã đóng gói xong lúc <strong>{{ $order->packed_at ? $order->packed_at->format('H:i d/m/Y') : '-' }}</strong>. Kiện hàng đang nằm tại kho và chờ bưu tá của đơn vị vận chuyển đến nhận.</p>
                                @break

                            @case('shipping')
                                <h6 class="text-warning fw-bold mb-1">🚚 Đơn hàng đang được giao đến bạn</h6>
                                <p class="text-dark small mb-0">
                                    Đơn hàng đã được bàn giao cho bưu tá vận chuyển lúc <strong>{{ $order->handed_over_at ? $order->handed_over_at->format('H:i d/m/Y') : '-' }}</strong>. 
                                    Hiện tại, đơn hàng đang trên đường di chuyển và <strong>dự kiến sẽ giao tới bạn trong vòng một vài giờ tới hoặc trong ngày hôm nay</strong>. 
                                    Bạn hãy chú ý điện thoại để shipper tiện liên hệ nhé!
                                </p>
                                @break

                            @case('failed')
                                <h6 class="text-danger fw-bold mb-1">⚠️ Giao hàng không thành công</h6>
                                <p class="text-dark small mb-1">
                                    Shipper báo cáo không thể giao kiện hàng này đến bạn. Nguyên nhân thường gặp do không liên hệ được số điện thoại hoặc sai lệch thông tin địa chỉ giao nhận.
                                </p>
                                
                                @php
                                    $failedProof = $order->proofs->where('type', 'failed_delivery')->last();
                                @endphp
                                @if($failedProof && $failedProof->note)
                                    <div class="bg-white p-2 rounded border small text-danger mb-2">
                                        <strong>Chi tiết từ shipper:</strong> {{ $failedProof->note }}
                                    </div>
                                @endif
                                
                                <p class="text-muted small mb-0">💡 <em>Đừng lo lắng! Nhân viên chăm sóc khách hàng sẽ kiểm tra lại đơn và lên lịch <strong>giao lại</strong> cho bạn sớm nhất có thể.</em></p>
                                @break

                            @case('completed')
                                <h6 class="text-success fw-bold mb-1">🎉 Giao hàng thành công</h6>
                                <p class="text-muted small mb-0">Đơn hàng đã được giao thành công tới tay bạn vào lúc <strong>{{ $order->delivered_at ? $order->delivered_at->format('H:i d/m/Y') : '-' }}</strong>. Cảm ơn bạn rất nhiều vì đã tin tưởng chọn mua sắm tại ShopGrids!</p>
                                @break

                            @case('cancelled')
                                <h6 class="text-danger fw-bold mb-1">❌ Đơn hàng đã bị hủy</h6>
                                <p class="text-muted small mb-1">Đơn hàng này đã bị hủy bỏ vào lúc <strong>{{ $order->cancelled_at ? $order->cancelled_at->format('H:i d/m/Y') : '-' }}</strong>.</p>
                                @if($order->fulfillment_status === 'cancelled')

                                <div class="alert alert-danger">

                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-x-circle-fill me-2"></i>Thông tin hủy đơn
                                    </h6>

                                    <p class="mb-2">
                                        <strong>Hủy bởi:</strong>

                                        @switch($order->cancelled_by)
                                            @case('admin')
                                                Quản trị viên
                                                @break

                                            @case('user')
                                                Người dùng
                                                @break

                                            @default
                                                Không xác định
                                        @endswitch
                                    </p>

                                    <p class="mb-0">
                                        <strong>Lý do hủy:</strong>

                                        {{ $order->cancel_reason ?? 'Không có lý do' }}
                                    </p>

                                </div>

                                @endif
                                @break

                            @default
                                <h6 class="text-secondary fw-bold mb-1">Trạng thái vận chuyển: {{ $order->fulfillment_status }}</h6>
                                <p class="text-muted small mb-0">Hệ thống đang cập nhật lộ trình di chuyển của đơn hàng.</p>
                        @endswitch
                    </div>

                    <div class="mt-4 pt-2">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2"></i>Lịch sử hành trình đơn hàng</h6>
                        
                        <div class="position-relative ps-3 ms-2 border-start border-2 border-primary-subtle" style="list-style: none;">
                            
                            <div class="position-relative mb-3">
                                <span class="position-absolute bg-success rounded-circle" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-success">🛍️ Đặt hàng thành công</div>
                                <div class="text-muted small">{{ $order->created_at->format('H:i d/m/Y') }}</div>
                            </div>

                            @if($order->confirmed_at)
                            <div class="position-relative mb-3">
                                <span class="position-absolute bg-success rounded-circle" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-success">✅ Đã xác nhận đơn hàng</div>
                                <div class="text-muted small">{{ $order->confirmed_at->format('H:i d/m/Y') }}</div>
                            </div>
                            @endif

                            @if($order->packed_at)
                            <div class="position-relative mb-3">
                                <span class="position-absolute bg-success rounded-circle" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-success">📦 Đóng gói sản phẩm hoàn tất</div>
                                <div class="text-muted small">{{ $order->packed_at->format('H:i d/m/Y') }}</div>
                            </div>
                            @endif

                            @if($order->handed_over_at)
                            <div class="position-relative mb-3">
                                <span class="position-absolute bg-success rounded-circle" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-success">🚚 Kiện hàng đã bàn giao cho bưu tá</div>
                                <div class="text-muted small">{{ $order->handed_over_at->format('H:i d/m/Y') }}</div>
                            </div>
                            @endif

                            @if($order->fulfillment_status === 'shipping' && !$order->delivered_at)
                            <div class="position-relative mb-3">
                                <span class="position-absolute bg-warning rounded-circle animate-pulse" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-warning">🔄 Đơn hàng đang được luân chuyển / đi giao</div>
                                <div class="text-muted small">Thời gian cập nhật gần nhất: {{ $order->updated_at->format('H:i d/m/Y') }}</div>
                            </div>
                            @endif

                            @php
                                $deliveredProof = $order->proofs
                                    ->where('type', 'delivered')
                                    ->last();
                            @endphp

                            @if($order->delivered_at)

                            <div class="position-relative mb-4">

                                <span
                                    class="position-absolute bg-success rounded-circle"
                                    style="width:12px;height:12px;left:-22px;top:6px;">
                                </span>

                                <div class="fw-bold small text-success">
                                    🎉 Kiện hàng đã giao thành công
                                </div>

                                <div class="text-muted small mb-2">
                                    {{ $order->delivered_at->format('H:i d/m/Y') }}
                                </div>

                                @if($deliveredProof)

                                    @if($deliveredProof->note)
                                        <div class="small text-muted mb-2">
                                            {{ $deliveredProof->note }}
                                        </div>
                                    @endif

                                    @if($deliveredProof->image_path)

                                        <a href="{{ asset('storage/'.$deliveredProof->image_path) }}"
                                        target="_blank">

                                            <img
                                                src="{{ asset('storage/'.$deliveredProof->image_path) }}"
                                                class="img-thumbnail"
                                                style="max-width:220px">

                                        </a>

                                    @endif

                                @endif

                            </div>

                            @endif

                            @if($order->fulfillment_status === 'failed')
                            <div class="position-relative mb-3">
                                <span class="position-absolute bg-danger rounded-circle" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-danger">⚠️ Shipper báo cáo giao hàng không thành công</div>
                                <div class="text-muted small">Cập nhật: {{ $order->updated_at->format('H:i d/m/Y') }}</div>
                            </div>
                            @endif

                            @if($order->cancelled_at)
                            <div class="position-relative mb-0">
                                <span class="position-absolute bg-danger rounded-circle" style="width: 12px; height: 12px; left: -22px; top: 6px;"></span>
                                <div class="fw-bold small text-danger">❌ Đơn hàng đã bị hủy bỏ</div>
                                <div class="text-muted small">{{ $order->cancelled_at->format('H:i d/m/Y') }}</div>
                            </div>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Sản phẩm</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Biến thể</th>
                                <th>Ảnh</th>
                                <th class="text-end">SL</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Sản phẩm' }}</td>
                                    <td>
                                        @if($item->variant)

                                            <div class="text-muted small">

                                            Màu:

                                            {{ $item->variant->color }}

                                            </div>

                                            <div class="text-muted small">

                                            Dung lượng:

                                            {{ $item->product->storage }}

                                            </div>

                                            @endif

                                            @if($item->imeis->count())

                                                <div class="mt-2">

                                                <div class="fw-semibold">

                                                IMEI

                                                </div>

                                                @foreach($item->imeis as $imei)

                                                <div class="small text-success">

                                                {{ $imei->imei }}

                                                </div>

                                                @endforeach

                                                </div>

                                                @endif
                                    </td>
                                    <td>
                                        @php

                                        $image =
                                            $item->variant?->image_path
                                            ?? $item->product?->image_path;

                                        @endphp
                                            @if($image)

                                                <img
                                                    src="{{ asset('storage/'.$image) }}"
                                                    width="80"
                                                    class="rounded border me-3">

                                                @endif
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->total, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    {{-- Thông tin người đặt hàng --}}
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-person-circle me-2"></i>Thông tin người đặt
                    </h6>

                    <p class="mb-1">
                        <strong>Họ tên:</strong>
                        {{ $order->customer_name }}
                    </p>

                    <p class="mb-1">
                        <strong>SĐT:</strong>
                        {{ $order->customer_phone }}
                    </p>

                    <p class="mb-3">
                        <strong>Địa chỉ:</strong>
                        {{ $order->shipping_address }}
                    </p>

                    <hr>

                    {{-- Thông tin người nhận --}}
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-geo-alt-fill me-2"></i>Thông tin người nhận
                    </h6>

                    @if($order->receiver)

                        <p class="mb-1">
                            <strong>Họ tên:</strong>
                            {{ $order->receiver->receiver_name }}
                        </p>

                        <p class="mb-1">
                            <strong>SĐT:</strong>
                            {{ $order->receiver->receiver_phone }}
                        </p>

                        <p class="mb-1">
                            <strong>Địa chỉ:</strong>
                            {{ $order->receiver->receiver_address }}
                        </p>

                        @if($order->receiver->receiver_note)
                            <p class="mb-3">
                                <strong>Ghi chú:</strong>
                                {{ $order->receiver->receiver_note }}
                            </p>
                        @endif

                    @else

                        <p class="text-muted">
                            Không có thông tin người nhận.
                        </p>

                    @endif

                    @if($order->shipment)
                        <hr>

                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-truck me-2"></i>Thông tin vận đơn
                        </h6>

                        <p class="mb-1">
                            <strong>Đơn vị:</strong>
                            {{ $order->shipment->shipping_unit ?? 'N/A' }}
                        </p>

                        <p class="mb-1">
                            <strong>Mã vận đơn:</strong>

                            @if($order->shipment->tracking_code)
                                <a href="{{ $order->shipment->tracking_url ?? '#' }}" target="_blank">
                                    {{ $order->shipment->tracking_code }}
                                </a>
                            @else
                                Chưa có
                            @endif
                        </p>

                        <p class="mb-0">
                            <strong>Trạng thái:</strong>
                            {{ $order->shipment->shipping_status ?? 'Chưa có' }}
                        </p>

                    @endif

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Tổng thanh toán</span>

                        <span class="fs-4 fw-bold text-danger">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @if($order->fulfillment_status === 'pending')

<div class="modal fade"
     id="cancelOrderModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <form action="{{ route('orders.cancel', $order->id) }}"
              method="POST"
              class="modal-content">

            @csrf

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">
                    Xác nhận hủy đơn hàng
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p class="mb-3">
                    Mã đơn:
                    <strong>{{ $order->order_code }}</strong>
                </p>

                <div class="mb-3">

                    <label class="form-label">
                        Lý do hủy đơn <span class="text-danger">*</span>
                    </label>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Chọn lý do hủy đơn <span class="text-danger">*</span>
                        </label>

                        @php
                            $cancelReasons = [
                                'Tôi muốn thay đổi địa chỉ nhận hàng',
                                'Tôi muốn thay đổi sản phẩm',
                                'Tôi đặt nhầm đơn hàng',
                                'Tôi tìm được giá tốt hơn',
                                'Thời gian giao hàng quá lâu',
                                'Không còn nhu cầu mua',
                            ];
                        @endphp

                        @foreach($cancelReasons as $index => $reason)

                            <div class="form-check mb-2">

                                <input
                                    class="form-check-input reason-radio"
                                    type="radio"
                                    name="reason_option"
                                    id="reason{{ $index }}"
                                    value="{{ $reason }}"
                                    required>

                                <label
                                    class="form-check-label"
                                    for="reason{{ $index }}">

                                    {{ $reason }}

                                </label>

                            </div>

                        @endforeach

                        <div class="form-check mb-3">

                            <input
                                class="form-check-input reason-radio"
                                type="radio"
                                name="reason_option"
                                id="reasonOther"
                                value="other">

                            <label
                                class="form-check-label"
                                for="reasonOther">

                                Khác (tự nhập)

                            </label>

                        </div>

                        <textarea
                            class="form-control d-none custom-reason"
                            rows="3"
                            placeholder="Nhập lý do của bạn..."></textarea>

                        <input
                            type="hidden"
                            name="cancel_reason"
                            class="cancel-reason-hidden">

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Đóng

                </button>

                <button type="submit"
                        class="btn btn-danger">

                    Xác nhận hủy

                </button>

            </div>

        </form>

    </div>

</div>

@endif

<script>

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.modal').forEach(function (modal) {

        const radios = modal.querySelectorAll('.reason-radio');

        const textarea = modal.querySelector('.custom-reason');

        const hidden = modal.querySelector('.cancel-reason-hidden');

        radios.forEach(function (radio) {

            radio.addEventListener('change', function () {

                if (this.value === 'other') {

                    textarea.classList.remove('d-none');

                    textarea.required = true;

                    hidden.value = '';

                } else {

                    textarea.classList.add('d-none');

                    textarea.required = false;

                    textarea.value = '';

                    hidden.value = this.value;

                }

            });

        });

        textarea.addEventListener('input', function () {

            hidden.value = this.value;

        });

    });

});

</script>
@endsection