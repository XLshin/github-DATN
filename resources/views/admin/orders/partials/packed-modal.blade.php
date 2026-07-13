<div class="modal fade" id="{{ $modalId ?? 'packedModal' }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.orders.markPacked', $order) }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Xác nhận đóng gói</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Đóng">
                </button>
            </div>

            <div class="modal-body">
                <p class="mb-3">
                    Mã đơn:
                    <strong>{{ $order->order_code }}</strong>
                </p>

                <div class="alert alert-info">
                    Với sản phẩm quản lý theo IMEI, hãy nhập hoặc chọn đúng IMEI thuộc biến thể khách đã đặt.
                    Sau khi xác nhận đóng gói, IMEI sẽ chuyển từ
                    <strong>available</strong>
                    sang
                    <strong>reserved</strong>.
                </div>

                @foreach($order->items as $item)
                    @php
                        $needsImei = ($item->product->product_type ?? null) === 'imei/serial';
                        $variantImeis = $availableImeisByVariant[$item->product_variant_id] ?? collect();
                        $assignedImeis = $item->imeis ?? collect();
                        $remaining = $needsImei ? max(0, (int) $item->quantity - $assignedImeis->count()) : 0;
                    @endphp

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold">
                            {{ $item->product->name ?? 'Sản phẩm đã xóa' }}
                            x {{ $item->quantity }}
                        </div>

                        <div class="text-muted small mb-2">
                            Biến thể:
                            @if($item->variant)
                                {{ $item->variant->color ?? '-' }}

                                @if(!empty($item->product?->storage))
                                    - {{ $item->product->storage }}
                                @endif
                            @else
                                -
                            @endif
                        </div>

                        @if($needsImei)
                            @if($assignedImeis->isNotEmpty())
                                <div class="alert alert-success mb-2">
                                    IMEI đã gán ({{ $assignedImeis->count() }}/{{ $item->quantity }}):
                                    <ul class="mb-0 ps-3">
                                        @foreach($assignedImeis as $assignedImei)
                                            <li>
                                                <strong>{{ $assignedImei->imei ?? '-' }}</strong>
                                                - {{ $assignedImei->status ?? '-' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($remaining > 0)
                                <label class="form-label">
                                    Nhập hoặc chọn thêm {{ $remaining }} IMEI còn thiếu <span class="text-danger">*</span>
                                </label>

                                @for($slot = 0; $slot < $remaining; $slot++)
                                    @php
                                        $datalistId = 'imei-options-' . $order->id . '-' . $item->id . '-' . $slot;
                                    @endphp

                                    <input type="text"
                                           name="imei_values[{{ $item->id }}][]"
                                           class="form-control mb-2"
                                           list="{{ $datalistId }}"
                                           value="{{ old('imei_values.' . $item->id . '.' . $slot) }}"
                                           placeholder="Nhập IMEI hoặc chọn từ danh sách gợi ý"
                                           required>

                                    <datalist id="{{ $datalistId }}">
                                        @foreach($variantImeis as $imei)
                                            <option value="{{ $imei->imei }}">
                                                {{ $imei->imei }}
                                            </option>
                                        @endforeach
                                    </datalist>
                                @endfor

                                <div class="form-text">
                                    Có {{ $variantImeis->count() }} IMEI available cho biến thể này.
                                </div>
                            @endif
                        @else
                            <div class="text-muted small">
                                Sản phẩm này quản lý theo số lượng, không cần nhập IMEI.
                            </div>
                        @endif
                    </div>
                @endforeach

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

                    <textarea name="note"
                              class="form-control"
                              rows="3"
                              placeholder="Nhập ghi chú nếu có">{{ old('note') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Đóng
                </button>

                <button type="submit" class="btn btn-primary">
                    Xác nhận đóng gói
                </button>
            </div>
        </form>
    </div>
</div>
