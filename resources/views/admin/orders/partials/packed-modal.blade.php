<div class="modal fade" id="packedModal{{ $order->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.markPacked', $order) }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Xác nhận đã đóng gói</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2">
                    Mã đơn:
                    <strong>{{ $order->order_code }}</strong>
                </p>

                <div class="mb-3">
                    <label class="form-label">
                        Ảnh minh chứng đã đóng gói <span class="text-danger">*</span>
                    </label>
                    <input type="file"
                           name="packed_image"
                           class="form-control"
                           accept="image/*"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Đóng
                </button>

                <button class="btn btn-primary">
                    Xác nhận đóng gói
                </button>
            </div>
        </form>
    </div>
</div>