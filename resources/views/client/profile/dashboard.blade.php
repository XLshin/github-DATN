@extends('layouts.app')

@section('title', 'Tài khoản của tôi')

@section('header')
<h1 class="h2 mb-1">Xin chào, {{ $user->name }}</h1>
@endsection

@section('content')
{{-- Thông tin cá nhân (dạng xem nhanh) --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="card-title mb-3">Thông tin cá nhân</h5>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Họ tên</small>
                        <strong>{{ $user->name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $user->email }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Số điện thoại</small>
                        <strong>{{ $user->phone ?? 'Chưa cập nhật' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Hạng thành viên</small>
                        <span class="badge bg-success">{{ $user->membership_level ?? 'Bronze' }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square"></i> Chỉnh sửa
            </a>
        </div>
    </div>
</div>

{{-- Quản lý địa chỉ nhận hàng --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Địa chỉ nhận hàng</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addressModal"
                id="addAddressBtn">
                <i class="bi bi-plus-lg"></i> Thêm mới
            </button>
        </div>

        @if(session('address_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('address_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @error('address_form')
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @enderror

        {{-- Danh sách địa chỉ --}}
        @forelse($addresses as $address)
        <div class="card mb-2 {{ $address->is_default ? 'border-primary bg-light' : '' }}">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start">
                <div>
                    <strong>{{ $address->label ?? 'Địa chỉ' }}</strong>
                    @if($address->is_default)
                    <span class="badge bg-primary ms-1">Mặc định</span>
                    @endif
                    <div class="text-muted small mt-1">
                        <i class="bi bi-person"></i> {{ $address->name }} &mdash;
                        <i class="bi bi-telephone"></i> {{ $address->phone }}<br>
                        <i class="bi bi-geo-alt"></i> {{ $address->address_line }}
                        @if($address->ward || $address->district || $address->city)
                        , {{ implode(', ', array_filter([$address->ward, $address->district, $address->city])) }}
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-1 mt-2 mt-sm-0">
                    {{-- Nút Sửa có chữ --}}
                    <button class="btn btn-sm btn-outline-warning edit-address-btn"
                        data-address="{{ json_encode($address->only(['id','label','name','phone','address_line','ward','district','city'])) }}">
                        <i class="bi bi-pencil"></i> Sửa
                    </button>

                    {{-- Nút Xóa có chữ --}}
                    <form action="{{ route('addresses.destroy', $address->id) }}" method="POST" class="d-inline delete-address-form">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-address">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </form>

                    @if(!$address->is_default)
                    <form action="{{ route('addresses.default', $address->id) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-primary" title="Đặt làm mặc định">
                            <i class="bi bi-check-circle"></i> Đặt mặc định
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-4 text-muted">
            <i class="bi bi-geo-alt fs-2"></i>
            <p class="mt-2">Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ đầu tiên!</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Các liên kết nhanh --}}
<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('password.change') }}" class="btn btn-outline-primary">
        <i class="bi bi-shield-lock"></i> Đổi mật khẩu
    </a>
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-bag"></i> Đơn hàng của tôi
    </a>
    @if ($user->isAdmin() || $user->isStaff())
    <a href="{{ route('admin.dashboard') }}" class="btn btn-warning">
        <i class="bi bi-speedometer2"></i> Quản trị
    </a>
    @endif
</div>

{{-- Modal thêm / sửa địa chỉ --}}
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="addressForm" novalidate>
            @csrf
            <div id="methodField"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Thêm địa chỉ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Hiển thị lỗi chung nếu có (từ session) --}}
                    <div id="addressFormErrors" class="alert alert-danger d-none"></div>

                    <input type="hidden" name="id" id="addressId">
                    <div class="mb-3">
                        <label class="form-label">Nhãn (VD: Nhà riêng, Công ty)</label>
                        <input type="text" name="label" class="form-control" id="addressLabel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required id="addressName">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" required id="addressPhone">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                        <input type="text" name="address_line" class="form-control" required id="addressLine">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Phường/Xã</label>
                            <input type="text" name="ward" class="form-control" id="addressWard">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quận/Huyện</label>
                            <input type="text" name="district" class="form-control" id="addressDistrict">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tỉnh/TP</label>
                            <input type="text" name="city" class="form-control" id="addressCity">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="addressSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Lưu địa chỉ
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const addressModal = document.getElementById('addressModal');
    const addressForm = document.getElementById('addressForm');
    const modalLabel = document.getElementById('addressModalLabel');
    const methodField = document.getElementById('methodField');
    const addressIdInput = document.getElementById('addressId');
    const errorsDiv = document.getElementById('addressFormErrors');
    const submitBtn = document.getElementById('addressSubmitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');

    // Hàm reset form về trạng thái thêm mới
    function resetToCreate() {
        addressForm.reset();
        addressIdInput.value = '';
        methodField.innerHTML = '';
        modalLabel.textContent = 'Thêm địa chỉ mới';
        addressForm.action = "{{ route('addresses.store') }}";
        clearErrors();
        addressForm.classList.remove('was-validated');
    }

    // Xóa thông báo lỗi
    function clearErrors() {
        errorsDiv.classList.add('d-none');
        errorsDiv.innerHTML = '';
        const invalidInputs = addressForm.querySelectorAll('.is-invalid');
        invalidInputs.forEach(el => el.classList.remove('is-invalid'));
    }

    // Hiển thị lỗi (từ response JSON nếu có)
    function showErrors(errors) {
        if (typeof errors === 'string') {
            errorsDiv.innerHTML = errors;
            errorsDiv.classList.remove('d-none');
        } else if (typeof errors === 'object') {
            let html = '<ul class="mb-0">';
            for (const [key, messages] of Object.entries(errors)) {
                messages.forEach(msg => html += `<li>${msg}</li>`);
            }
            html += '</ul>';
            errorsDiv.innerHTML = html;
            errorsDiv.classList.remove('d-none');
        }
    }

    // Khi modal được mở (dành cho nút "Thêm mới")
    document.getElementById('addAddressBtn').addEventListener('click', resetToCreate);

    // Xử lý nút sửa
    document.querySelectorAll('.edit-address-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const addr = JSON.parse(this.dataset.address);
            addressIdInput.value = addr.id;
            document.getElementById('addressLabel').value = addr.label || '';
            document.getElementById('addressName').value = addr.name;
            document.getElementById('addressPhone').value = addr.phone;
            document.getElementById('addressLine').value = addr.address_line;
            document.getElementById('addressWard').value = addr.ward || '';
            document.getElementById('addressDistrict').value = addr.district || '';
            document.getElementById('addressCity').value = addr.city || '';

            methodField.innerHTML = '@method("PUT")';
            modalLabel.textContent = 'Cập nhật địa chỉ';
            addressForm.action = "{{ route('addresses.update', ':id') }}".replace(':id', addr.id);
            clearErrors();
            addressForm.classList.remove('was-validated');

            // Mở modal thủ công
            const modal = new bootstrap.Modal(addressModal);
            modal.show();
        });
    });

    // Xác nhận xóa
    document.querySelectorAll('.btn-delete-address').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) {
                this.closest('form').submit();
            }
        });
    });

    // Xử lý submit form bằng AJAX để hiển thị lỗi trong modal mà không reload trang
    addressForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();
        addressForm.classList.add('was-validated');

        // Validate HTML5
        if (!addressForm.checkValidity()) {
            return;
        }

        // Disable nút, hiện spinner
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData(addressForm);
        // Nếu là PUT, Laravel yêu cầu method spoofing, chúng ta có _method trong form
        fetch(addressForm.action, {
                method: 'POST', // vì Laravel nhận POST với _method PUT
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                if (data.success) {
                    // Reload trang để hiển thị địa chỉ mới
                    window.location.reload();
                } else {
                    showErrors(data.errors || 'Có lỗi xảy ra, vui lòng thử lại.');
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                showErrors('Lỗi kết nối, vui lòng thử lại.');
            });
    });

    // Đảm bảo khi đóng modal cũng reset (phòng trường hợp mở bằng cách khác)
    addressModal.addEventListener('hidden.bs.modal', function() {
        // Không reset ở đây để giữ trạng thái khi sửa lỗi, thay vào đó reset khi mở bằng nút Thêm.
    });
</script>
@endpush
@endsection