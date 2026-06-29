@extends('layouts.admin')

@section('title', 'Nhập kho IMEI / Serial')
@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Nhập kho IMEI / Serial')
@section('page_subtitle', 'Nhập kho cho các sản phẩm quản lý bằng IMEI hoặc Serial.')

@section('heading_actions') <a href="{{ route('admin.stocks') }}" class="btn btn-light btn-sm"> <i class="bi bi-arrow-left"></i>
Quay lại </a>
@endsection

@section('content')

<section class="panel">

<div class="panel-header">
    <div>
        <h5 class="mb-1">Thông tin nhập kho</h5>
        <div class="text-muted small">
            Chọn biến thể sản phẩm và nhập danh sách IMEI / Serial.
        </div>
    </div>
</div>

<div class="p-3">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.imeis.store') }}"
        method="POST"
        style="max-width:700px;">

        @csrf

        <div class="mb-3">

            <label class="form-label">
                Chọn biến thể sản phẩm
                <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                id="variantSearchInput"
                class="form-control mb-2"
                placeholder="Tìm theo tên sản phẩm, màu sắc hoặc dung lượng">

            <select
                id="variantSelect"
                name="product_variant_id"
                class="form-select"
                size="5">

                @foreach($imeiVariants as $variant)

<option
    value="{{ $variant->id }}"
    data-search="{{ strtolower(
        $variant->product->name . ' ' .
        $variant->color . ' ' .
        $variant->storage
    ) }}"
>
{{ trim($variant->product->name . ' - ' . ($variant->color ?? '---') . ' - ' . ($variant->storage ?? '---')) }}
</option>

                @endforeach

            </select>

            @error('product_variant_id')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="mb-3">

            <label class="form-label">
                Danh sách IMEI / Serial
                <span class="text-danger">*</span>
            </label>

            <textarea
                name="imeis"
                rows="5"
                class="form-control"
                placeholder="Mỗi dòng một IMEI hoặc Serial
123456789012345
123456789012346
123456789012347">{{ old('imeis') }}</textarea>
            <div class="form-text">
                Mỗi dòng nhập một IMEI hoặc Serial.
            </div>

            @error('imeis')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="d-flex gap-2">

            <button
                type="submit"
                class="btn btn-primary btn-sm">

                <i class="bi bi-check-lg"></i>
                Nhập kho

            </button>

            <a
                href="{{ route('admin.stocks') }}"
                class="btn btn-light btn-sm">

                Hủy

            </a>

        </div>

    </form>

</div>

</section>

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput =
        document.getElementById('variantSearchInput');

    const select =
        document.getElementById('variantSelect');

    const options =
        Array.from(select.options);

    searchInput.addEventListener('input', function () {

        const keyword =
            this.value.trim().toLowerCase();

        options.forEach(option => {

            const search =
                option.dataset.search || '';

            option.hidden =
                keyword &&
                !search.includes(keyword);

        });

    });

});
const searchInput =
    document.getElementById('variantSearchInput');

const select =
    document.getElementById('variantSelect');

select.addEventListener('change', function () {

    const selectedOption =
        this.options[this.selectedIndex];

    searchInput.value =
        selectedOption.textContent.trim();

});
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('variantSearchInput');
    const select = document.getElementById('variantSelect');

    const options = Array.from(select.options);

    // mặc định ẩn
    select.style.display = 'none';

    // click vào input thì hiện danh sách
    searchInput.addEventListener('focus', function () {
        select.style.display = 'block';
    });

    // tìm kiếm
    searchInput.addEventListener('input', function () {

        const keyword = this.value.trim().toLowerCase();

        select.style.display = 'block';

        options.forEach(option => {

            const search = option.dataset.search || '';

            option.hidden =
                keyword &&
                !search.includes(keyword);

        });

    });

    // chọn xong thì đưa lên input rồi ẩn select
    select.addEventListener('change', function () {

        const selectedOption =
            this.options[this.selectedIndex];

        searchInput.value =
            selectedOption.textContent.trim();

        select.style.display = 'none';

    });

    // click ra ngoài thì ẩn
    document.addEventListener('click', function (e) {

        if (
            !searchInput.contains(e.target) &&
            !select.contains(e.target)
        ) {
            select.style.display = 'none';
        }

    });

});
</script>
@endpush

@endsection
