<div class="modal fade"
     id="{{ $modalId ?? ('deliveredModal-' . $order->id) }}"
     tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.markDelivered', $order) }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Xác nhận giao hàng thành công</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Đóng">
                </button>
            </div>

            <div class="modal-body">
                <p class="mb-2">
                    Mã đơn:
                    <strong>{{ $order->order_code }}</strong>
                </p>

                <div class="mb-3">
                    <label class="form-label">
                        Ảnh minh chứng đã giao hàng <span class="text-danger">*</span>
                    </label>

                    <input type="file"
                           name="delivered_image"
                           class="form-control"
                           accept="image/*"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>

                    <textarea name="note"
                              class="form-control"
                              rows="3"
                              placeholder="Nhập ghi chú nếu có"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Đóng
                </button>

                <button type="submit" class="btn btn-success">
                    Xác nhận đã giao
                </button>
            </div>
        </form>
    </div>
</div>