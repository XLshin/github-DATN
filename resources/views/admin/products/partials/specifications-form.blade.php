@php
    $oldSpecifications = old('specifications');

    if (is_array($oldSpecifications)) {
        $specificationRows = collect($oldSpecifications);
    } elseif (isset($productGroup)) {
        $specificationRows = $productGroup->specifications->map(function ($specification) {
            return [
                'group_name' => $specification->group_name,
                'name' => $specification->name,
                'value' => $specification->value,
            ];
        });
    } else {
        $specificationRows = collect();
    }
@endphp

<section class="panel mb-3">
    <div class="panel-header">
        <div>
            <h5 class="mb-1">Thông số kỹ thuật</h5>
            <div class="text-muted small">Nhập các thông số hiển thị ở trang chi tiết sản phẩm.</div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addSpecification">
            <i class="bi bi-plus-lg"></i> Thêm thông số
        </button>
    </div>

    <div class="p-3">
        <div class="alert alert-info d-flex gap-2 align-items-start">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                <div class="fw-semibold">Thông số kỹ thuật là phần bổ sung</div>
                <div class="small">
                    Có thể để trống khi tạo sản phẩm. Phần này dùng để hiển thị chi tiết ở trang khách hàng và có thể cập nhật sau.
                </div>
            </div>
        </div>

        @if(isset($specificationSourceGroups) && $specificationSourceGroups->isNotEmpty())
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-9">
                <label class="form-label small">Sao chép thông số từ sản phẩm khác</label>
                <select id="specificationCopySource"
                    class="form-select form-select-sm"
                    data-url-template="{{ route('admin.product-groups.specifications', ['productGroup' => '__PRODUCT_GROUP__']) }}">
                    <option value="">-- Chọn sản phẩm nguồn --</option>
                    @foreach($specificationSourceGroups as $sourceGroup)
                        <option value="{{ $sourceGroup->id }}">{{ $sourceGroup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="button" class="btn btn-light btn-sm" id="copySpecifications">
                    <i class="bi bi-copy"></i> Sao chép
                </button>
            </div>
        </div>
        @endif

        <div id="specificationsContainer">
            @forelse($specificationRows as $index => $specification)
                <div class="row g-2 align-items-end mb-2 specification-row" data-index="{{ $index }}">
                    <div class="col-md-3">
                        <label class="form-label small">Nhóm</label>
                        <input type="text"
                            name="specifications[{{ $index }}][group_name]"
                            value="{{ $specification['group_name'] ?? '' }}"
                            class="form-control form-control-sm specification-group"
                            list="specificationGroupSuggestions"
                            placeholder="VD: Màn hình">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Tên thông số</label>
                        <input type="text"
                            name="specifications[{{ $index }}][name]"
                            value="{{ $specification['name'] ?? '' }}"
                            class="form-control form-control-sm"
                            placeholder="VD: Kích thước">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Giá trị</label>
                        <input type="text"
                            name="specifications[{{ $index }}][value]"
                            value="{{ $specification['value'] ?? '' }}"
                            class="form-control form-control-sm"
                            placeholder="VD: 6.1 inch">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-light btn-sm remove-specification" title="Xóa">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0" id="noSpecificationMsg">Chưa có thông số nào.</p>
            @endforelse
        </div>

        <datalist id="specificationGroupSuggestions"></datalist>

        @error('specifications')
            <div class="alert alert-danger py-2 mt-2 mb-0">{{ $message }}</div>
        @enderror
    </div>
</section>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('specificationsContainer');
    const addButton = document.getElementById('addSpecification');
    const copyButton = document.getElementById('copySpecifications');
    const copySourceSelect = document.getElementById('specificationCopySource');
    const dataList = document.getElementById('specificationGroupSuggestions');
    let specificationIndex = container
        ? Array.from(container.querySelectorAll('.specification-row')).length
        : 0;

    function refreshGroupSuggestions() {
        if (!container || !dataList) return;

        const groups = Array.from(container.querySelectorAll('.specification-group'))
            .map(input => input.value.trim())
            .filter(Boolean);
        const uniqueGroups = Array.from(new Set(groups));

        dataList.innerHTML = uniqueGroups
            .map(group => `<option value="${group.replace(/"/g, '&quot;')}"></option>`)
            .join('');
    }

    function lastGroupName() {
        if (!container) return '';

        const groups = Array.from(container.querySelectorAll('.specification-group'))
            .map(input => input.value.trim())
            .filter(Boolean);

        return groups.length ? groups[groups.length - 1] : '';
    }

    function escapeAttribute(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
    }

    function addSpecificationRow(groupName = '', name = '', value = '') {
        if (!container) return;

        const emptyMessage = document.getElementById('noSpecificationMsg');
        if (emptyMessage) emptyMessage.remove();

        const index = specificationIndex++;
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 specification-row';
        row.dataset.index = index;
        row.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small">Nhóm</label>
                <input type="text"
                    name="specifications[${index}][group_name]"
                    value="${escapeAttribute(groupName)}"
                    class="form-control form-control-sm specification-group"
                    list="specificationGroupSuggestions"
                    placeholder="VD: Màn hình">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tên thông số</label>
                <input type="text"
                    name="specifications[${index}][name]"
                    value="${escapeAttribute(name)}"
                    class="form-control form-control-sm"
                    placeholder="VD: Kích thước">
            </div>
            <div class="col-md-5">
                <label class="form-label small">Giá trị</label>
                <input type="text"
                    name="specifications[${index}][value]"
                    value="${escapeAttribute(value)}"
                    class="form-control form-control-sm"
                    placeholder="VD: 6.1 inch">
            </div>
            <div class="col-md-1 d-grid">
                <button type="button" class="btn btn-light btn-sm remove-specification" title="Xóa">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>`;

        container.appendChild(row);
        row.querySelector('input[name$="[name]"]').focus();
        refreshGroupSuggestions();
    }

    function clearSpecificationRows() {
        if (!container) return;
        container.querySelectorAll('.specification-row').forEach(row => row.remove());
    }

    addButton?.addEventListener('click', function () {
        addSpecificationRow(lastGroupName());
    });

    if (copyButton && copySourceSelect) {
        copyButton.addEventListener('click', function () {
            const productId = copySourceSelect.value;
            const urlTemplate = copySourceSelect.dataset.urlTemplate || '';

            if (!productId || !urlTemplate) return;

            const hasRows = container && container.querySelector('.specification-row');
            if (hasRows && !confirm('Xóa các thông số hiện tại rồi sao chép từ sản phẩm đã chọn?')) {
                return;
            }

            if (hasRows) {
                clearSpecificationRows();
            }

            fetch(urlTemplate.replace('__PRODUCT_GROUP__', productId), {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(specifications => {
                    specifications.forEach(specification => {
                        addSpecificationRow(
                            specification.group_name || '',
                            specification.name || '',
                            specification.value || ''
                        );
                    });
                })
                .catch(error => {
                    console.error(error);
                    alert('Không thể sao chép thông số kỹ thuật.');
                });
        });
    }

    if (container) {
        container.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-specification');
            if (!button) return;

            button.closest('.specification-row')?.remove();
            refreshGroupSuggestions();
        });

        container.addEventListener('input', function (event) {
            if (event.target.classList.contains('specification-group')) {
                refreshGroupSuggestions();
            }
        });
    }

    refreshGroupSuggestions();
});
</script>
@endpush
@endonce
