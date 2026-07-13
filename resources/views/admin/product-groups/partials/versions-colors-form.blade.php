@php
    $editingGroup = $productGroup ?? null;

    if (old('versions') !== null) {
        $versionRows = collect(old('versions'))->values();
    } elseif ($editingGroup) {
        $versionRows = $editingGroup->products->map(fn ($product) => [
            'id' => $product->id,
            'storage' => $product->storage,
            'name' => $product->name,
            'price' => $product->price,
            'description' => $product->description,
            'has_imeis' => $product->variants->contains(fn ($variant) => (int) ($variant->imeis_count ?? 0) > 0),
            'has_stock' => $product->variants->contains(fn ($variant) => (int) $variant->stock_quantity > 0),
        ])->values();
    } else {
        $versionRows = collect([
            ['storage' => '', 'name' => '', 'price' => '', 'description' => ''],
        ]);
    }

    if ($versionRows->isEmpty()) {
        $versionRows = collect([
            ['storage' => '', 'name' => '', 'price' => '', 'description' => ''],
        ]);
    }

    if (old('colors') !== null) {
        $colorRows = collect(old('colors'))->values();
    } elseif ($editingGroup) {
        $colorRows = $editingGroup->products
            ->flatMap(fn ($product) => $product->variants)
            ->filter(fn ($variant) => filled($variant->color))
            ->groupBy('color')
            ->map(fn ($variants, $color) => [
                'original_name' => $color,
                'name' => $color,
                'image_path' => optional($variants->firstWhere('image_path', '!=', null))->image_path,
                'has_imeis' => $variants->contains(fn ($variant) => (int) ($variant->imeis_count ?? 0) > 0),
                'has_stock' => $variants->contains(fn ($variant) => (int) $variant->stock_quantity > 0),
            ])
            ->values();
    } else {
        $colorRows = collect([
            ['name' => ''],
        ]);
    }

    if ($colorRows->isEmpty()) {
        $colorRows = collect([
            ['name' => ''],
        ]);
    }

    $showColorPreviewColumn = $editingGroup !== null;
@endphp

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Phiên bản sản phẩm</h5>
            <div class="text-muted small">Thêm các dung lượng hoặc phiên bản như 128GB, 256GB, 30W, Mặc định.</div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addVersion">
            <i class="bi bi-plus-lg"></i> Thêm phiên bản
        </button>
    </div>

    <div class="p-3">
        <div class="alert alert-info d-flex gap-2 align-items-start">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                <div class="fw-semibold">Lưu ý khi nhập phiên bản</div>
                <div class="small">
                    Mỗi dòng là một phiên bản bán riêng. Ví dụ <strong>256GB</strong> sẽ gợi ý tên
                    <strong>iPhone 17 Pro Max 256GB</strong>, nhưng bạn vẫn có thể sửa lại trước khi lưu.
                    Phiên bản đã có IMEI sẽ bị khóa tên, dung lượng và không thể xóa.
                </div>
            </div>
        </div>
    </div>

    <div class="px-3 pb-3" id="versionsContainer">
        @foreach($versionRows as $index => $version)
        @php($isExistingVersion = !empty($version['id']))
        @php($versionHasImeis = !empty($version['has_imeis']))
        @php($versionHasStock = !empty($version['has_stock']))
        @php($versionLocked = $versionHasImeis || $versionHasStock)
        <div class="border rounded p-3 mb-2 version-row" data-index="{{ $index }}" data-existing="{{ $isExistingVersion ? '1' : '0' }}">
            @if($isExistingVersion)
                <input type="hidden" name="versions[{{ $index }}][id]" value="{{ $version['id'] }}">
            @endif

            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <div class="fw-semibold small text-muted">
                    Phiên bản #<span class="version-number">{{ $loop->iteration }}</span>
                    @if($isExistingVersion)
                        <span class="badge bg-light text-muted ms-1">Đang có</span>
                    @endif
                    @if($versionHasImeis)
                        <span class="badge bg-warning text-dark ms-1">Đã có IMEI</span>
                    @endif
                    @if($versionHasStock)
                        <span class="badge bg-info text-dark ms-1">Đã có tồn kho</span>
                    @endif
                </div>
                <button type="button"
                    class="btn btn-light btn-sm remove-version"
                    title="{{ $versionLocked ? 'Phiên bản đã có IMEI hoặc tồn kho nên không thể xóa' : 'Xóa phiên bản' }}"
                    @disabled($versionLocked)>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            @if($versionLocked)
                <div class="alert alert-warning py-2 small mb-2">
                    Phiên bản này đã có IMEI hoặc tồn kho nên không thể sửa tên hoặc dung lượng. Bạn vẫn có thể chỉnh giá, mô tả và trạng thái hiển thị.
                </div>
            @endif

            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label small">Dung lượng / phiên bản <span class="text-danger">*</span></label>
                    <input type="text"
                        name="versions[{{ $index }}][storage]"
                        value="{{ $version['storage'] ?? '' }}"
                        class="form-control form-control-sm version-storage @error("versions.$index.storage") is-invalid @enderror"
                        placeholder="VD: 256GB"
                        @readonly($versionLocked)>
                    @error("versions.$index.storage")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-5">
                    <label class="form-label small">Tên phiên bản <span class="text-danger">*</span></label>
                    <input type="text"
                        name="versions[{{ $index }}][name]"
                        value="{{ $version['name'] ?? '' }}"
                        class="form-control form-control-sm version-name @error("versions.$index.name") is-invalid @enderror"
                        placeholder="VD: iPhone 17 Pro Max 256GB"
                        @readonly($versionLocked)>
                    @error("versions.$index.name")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Giá base <span class="text-danger">*</span></label>
                    <input type="number"
                        name="versions[{{ $index }}][price]"
                        value="{{ $version['price'] ?? '' }}"
                        min="0"
                        class="form-control form-control-sm @error("versions.$index.price") is-invalid @enderror"
                        placeholder="0">
                    @error("versions.$index.price")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label small">Mô tả phiên bản</label>
                    <input type="text"
                        name="versions[{{ $index }}][description]"
                        value="{{ $version['description'] ?? '' }}"
                        class="form-control form-control-sm @error("versions.$index.description") is-invalid @enderror"
                        placeholder="Để trống nếu dùng mô tả chung">
                    @error("versions.$index.description")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @error('versions')
    <div class="px-3 pb-3">
        <div class="alert alert-danger py-2 mb-0">{{ $message }}</div>
    </div>
    @enderror
</section>

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Màu sắc dùng chung</h5>
            <div class="text-muted small">Các màu này sẽ tự sinh cho toàn bộ phiên bản phía trên.</div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addColor">
            <i class="bi bi-plus-lg"></i> Thêm màu
        </button>
    </div>

    <div class="p-3">
        <div class="alert alert-info d-flex gap-2 align-items-start">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                <div class="fw-semibold">Lưu ý khi nhập màu sắc</div>
                <div class="small">
                    Danh sách màu này dùng chung cho toàn bộ phiên bản. Khi lưu, hệ thống tự tạo đủ tổ hợp
                    <strong>phiên bản x màu</strong>; giá cộng thêm của màu sẽ mặc định là <strong>0</strong>.
                    Màu đã có IMEI sẽ bị khóa tên và không thể xóa.
                </div>
            </div>
        </div>
    </div>

    <div class="px-3 pb-3" id="colorsContainer" data-show-preview="{{ $showColorPreviewColumn ? '1' : '0' }}">
        @foreach($colorRows as $index => $color)
        @php($isExistingColor = !empty($color['original_name']))
        @php($colorHasImeis = !empty($color['has_imeis']))
        @php($colorHasStock = !empty($color['has_stock']))
        @php($colorLocked = $colorHasImeis || $colorHasStock)
        <div class="row g-2 align-items-end mb-2 color-row" data-index="{{ $index }}" data-existing="{{ $isExistingColor ? '1' : '0' }}">
            @if($isExistingColor)
                <input type="hidden" name="colors[{{ $index }}][original_name]" value="{{ $color['original_name'] }}">
            @endif
            <input type="hidden" name="colors[{{ $index }}][delete_image]" value="0" class="delete-color-image-input">

            <div class="col-md-5">
                <label class="form-label small">Tên màu <span class="text-danger">*</span></label>
                <input type="text"
                    name="colors[{{ $index }}][name]"
                    value="{{ $color['name'] ?? '' }}"
                    class="form-control form-control-sm @error("colors.$index.name") is-invalid @enderror"
                    placeholder="VD: Titan Đen"
                    @readonly($colorLocked)>
                @error("colors.$index.name")<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($colorLocked)
                    <div class="form-text text-warning">Màu này đã có IMEI hoặc tồn kho nên không thể đổi tên hoặc xóa.</div>
                @endif
            </div>

            <div class="{{ $showColorPreviewColumn ? 'col-md-5' : 'col-md-6' }}">
                <label class="form-label small">Ảnh màu</label>
                <input type="file"
                    name="colors[{{ $index }}][image]"
                    class="form-control form-control-sm @error("colors.$index.image") is-invalid @enderror"
                    accept="image/*">
                @error("colors.$index.image")<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if($showColorPreviewColumn)
            <div class="col-md-1">
                @if(!empty($color['image_path']))
                    <div class="position-relative color-image-preview" style="width: 44px; height: 44px;">
                        <img src="{{ Storage::url($color['image_path']) }}"
                            alt="{{ $color['name'] ?? 'Ảnh màu' }}"
                            class="rounded border"
                            style="width: 44px; height: 44px; object-fit: cover;">
                        <button type="button"
                            class="btn btn-danger btn-sm remove-color-image"
                            title="Xóa ảnh màu"
                            style="position: absolute; top: -8px; right: -8px; width: 22px; height: 22px; padding: 0; line-height: 1;">
                            &times;
                        </button>
                    </div>
                @else
                    <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted color-image-placeholder"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-image"></i>
                    </div>
                @endif
            </div>
            @endif

            <div class="col-md-1 d-grid">
                <button type="button"
                    class="btn btn-light btn-sm remove-color"
                    title="{{ $colorLocked ? 'Màu đã có IMEI hoặc tồn kho nên không thể xóa' : 'Xóa màu' }}"
                    @disabled($colorLocked)>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    @error('colors')
    <div class="px-3 pb-3">
        <div class="alert alert-danger py-2 mb-0">{{ $message }}</div>
    </div>
    @enderror
</section>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productNameInput = document.querySelector('input[name="name"]');
    const versionsContainer = document.getElementById('versionsContainer');
    const colorsContainer = document.getElementById('colorsContainer');
    const addVersionButton = document.getElementById('addVersion');
    const addColorButton = document.getElementById('addColor');
    const showColorPreviewColumn = colorsContainer?.dataset.showPreview === '1';

    let versionIndex = versionsContainer ? versionsContainer.querySelectorAll('.version-row').length : 0;
    let colorIndex = colorsContainer ? colorsContainer.querySelectorAll('.color-row').length : 0;

    function suggestedVersionName(storage) {
        return [productNameInput?.value.trim(), storage.trim()].filter(Boolean).join(' ');
    }

    function syncVersionName(row) {
        const storageInput = row.querySelector('.version-storage');
        const nameInput = row.querySelector('.version-name');
        if (!storageInput || !nameInput || nameInput.readOnly) return;

        const previousAuto = nameInput.dataset.autoName || '';
        const nextAuto = suggestedVersionName(storageInput.value);

        if (!nameInput.value.trim() || nameInput.value.trim() === previousAuto) {
            nameInput.value = nextAuto;
        }

        nameInput.dataset.autoName = nextAuto;
    }

    function refreshVersionNumbers() {
        if (!versionsContainer) return;
        versionsContainer.querySelectorAll('.version-row').forEach((row, index) => {
            const number = row.querySelector('.version-number');
            if (number) number.textContent = index + 1;
        });
    }

    function colorImagePlaceholder() {
        const placeholder = document.createElement('div');
        placeholder.className = 'rounded border bg-light d-flex align-items-center justify-content-center text-muted color-image-placeholder';
        placeholder.style.width = '44px';
        placeholder.style.height = '44px';
        placeholder.innerHTML = '<i class="bi bi-image"></i>';
        return placeholder;
    }

    function addVersionRow() {
        if (!versionsContainer) return;

        const index = versionIndex++;
        const row = document.createElement('div');
        row.className = 'border rounded p-3 mb-2 version-row';
        row.dataset.index = index;
        row.dataset.existing = '0';
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <div class="fw-semibold small text-muted">Phiên bản #<span class="version-number"></span></div>
                <button type="button" class="btn btn-light btn-sm remove-version" title="Xóa phiên bản">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label small">Dung lượng / phiên bản <span class="text-danger">*</span></label>
                    <input type="text" name="versions[${index}][storage]"
                        class="form-control form-control-sm version-storage" placeholder="VD: 256GB">
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Tên phiên bản <span class="text-danger">*</span></label>
                    <input type="text" name="versions[${index}][name]"
                        class="form-control form-control-sm version-name" placeholder="VD: iPhone 17 Pro Max 256GB">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Giá base <span class="text-danger">*</span></label>
                    <input type="number" name="versions[${index}][price]"
                        class="form-control form-control-sm" min="0" placeholder="0">
                </div>
                <div class="col-12">
                    <label class="form-label small">Mô tả phiên bản</label>
                    <input type="text" name="versions[${index}][description]"
                        class="form-control form-control-sm" placeholder="Để trống nếu dùng mô tả chung">
                </div>
            </div>`;

        versionsContainer.appendChild(row);
        refreshVersionNumbers();
        syncVersionName(row);
        row.querySelector('.version-storage')?.focus();
    }

    function addColorRow() {
        if (!colorsContainer) return;

        const index = colorIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 color-row';
        row.dataset.index = index;
        row.dataset.existing = '0';
        row.innerHTML = `
            <input type="hidden" name="colors[${index}][delete_image]" value="0" class="delete-color-image-input">
            <div class="col-md-5">
                <label class="form-label small">Tên màu <span class="text-danger">*</span></label>
                <input type="text" name="colors[${index}][name]"
                    class="form-control form-control-sm" placeholder="VD: Titan Đen">
            </div>
            <div class="${showColorPreviewColumn ? 'col-md-5' : 'col-md-6'}">
                <label class="form-label small">Ảnh màu</label>
                <input type="file" name="colors[${index}][image]"
                    class="form-control form-control-sm" accept="image/*">
            </div>
            ${showColorPreviewColumn ? '<div class="col-md-1"></div>' : ''}
            <div class="col-md-1 d-grid">
                <button type="button" class="btn btn-light btn-sm remove-color" title="Xóa màu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>`;

        colorsContainer.appendChild(row);
        row.querySelector('input[name$="[name]"]')?.focus();
    }

    if (productNameInput && versionsContainer) {
        productNameInput.addEventListener('input', function () {
            versionsContainer.querySelectorAll('.version-row').forEach(syncVersionName);
        });
    }

    versionsContainer?.addEventListener('input', function (event) {
        const row = event.target.closest('.version-row');
        if (!row) return;

        if (event.target.classList.contains('version-storage')) {
            syncVersionName(row);
        }

        if (event.target.classList.contains('version-name')) {
            event.target.dataset.autoName = suggestedVersionName(row.querySelector('.version-storage')?.value || '');
        }
    });

    versionsContainer?.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-version');
        if (!button || button.disabled) return;

        if (versionsContainer.querySelectorAll('.version-row').length <= 1) {
            return;
        }

        button.closest('.version-row')?.remove();
        refreshVersionNumbers();
    });

    colorsContainer?.addEventListener('click', function (event) {
        const imageButton = event.target.closest('.remove-color-image');
        if (imageButton) {
            if (!confirm('Bạn có chắc muốn xóa ảnh màu này không? File ảnh trong storage cũng sẽ bị xóa khi lưu.')) {
                return;
            }

            const row = imageButton.closest('.color-row');
            const input = row?.querySelector('.delete-color-image-input');
            if (input) input.value = '1';
            imageButton.closest('.color-image-preview')?.replaceWith(colorImagePlaceholder());
            return;
        }

        const button = event.target.closest('.remove-color');
        if (!button || button.disabled) return;

        if (colorsContainer.querySelectorAll('.color-row').length <= 1) {
            return;
        }

        button.closest('.color-row')?.remove();
    });

    addVersionButton?.addEventListener('click', addVersionRow);
    addColorButton?.addEventListener('click', addColorRow);

    versionsContainer?.querySelectorAll('.version-row').forEach(syncVersionName);
    refreshVersionNumbers();
});
</script>
@endpush
@endonce
