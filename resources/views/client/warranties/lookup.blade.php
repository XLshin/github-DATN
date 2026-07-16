@extends('layouts.client')

@section('title', 'Tra cứu bảo hành')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Tra cứu bảo hành</h1>

    <form method="POST" action="{{ route('warranties.lookup.post') }}" class="mb-4">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">IMEI</label>
                <input id="imei-input" name="imei" class="form-control" value="{{ old('imei') }}" list="user-imeis" placeholder="Nhập IMEI để tra cứu">
                <datalist id="user-imeis">
                    @if(!empty($userImeis))
                        @foreach($userImeis as $ui)
                            <option value="{{ $ui }}"></option>
                        @endforeach
                    @endif
                </datalist>
                @if(!empty($userImeis))
                    <div class="mt-2">
                        <small class="text-muted">IMEI của bạn: </small>
                        @foreach($userImeis as $ui)
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-imei m-1" data-imei="{{ $ui }}">{{ $ui }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-md-5">
                <label class="form-label">Mã đơn hàng</label>
                <input name="order_code" class="form-control" value="{{ old('order_code') }}" placeholder="Hoặc nhập mã đơn hàng">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Tra cứu</button>
            </div>
        </div>
    </form>

    @if(isset($imei) && $imei)
        <div class="card mb-3">
            <div class="card-body">
                <h5>IMEI: {{ $imei->imei }}</h5>
                <p>Trạng thái: {{ $imei->status }}</p>
            </div>
        </div>
    @endif

@push('scripts')
<script>
document.addEventListener('click', function(e){
    var btn = e.target.closest('.btn-imei');
    if(!btn) return;
    var imei = btn.getAttribute('data-imei');
    var input = document.getElementById('imei-input');
    if(input){ input.value = imei; }
});
</script>
@endpush

    @if(isset($currentWarranty) && $currentWarranty)
        <div class="alert alert-warning">Hiện tại có phiếu bảo hành đang xử lý: <strong>{{ $currentWarranty->warranty_code ?? '---' }}</strong></div>
    @endif

    @if(isset($warranties) && $warranties->isNotEmpty())
        <h4 class="mb-3">Lịch sử phiếu bảo hành</h4>
        <div class="list-group">
            @foreach($warranties as $w)
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">{{ $w->warranty_code ?? 'Phiếu #' . $w->id }}</h5>
                        <small>{{ $w->created_at?->format('d/m/Y') }}</small>
                    </div>
                    <p class="mb-1">Trạng thái: {{ $w->status }}</p>
                    <p class="mb-1">Ghi chú khách hàng: {{ $w->customer_note ?? '-' }}</p>
                    <small>Đơn hàng: {{ $w->order?->order_code ?? '-' }}</small>
                    <div class="mt-2">
                        <a href="{{ route('warranties.show', $w) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(request()->isMethod('post'))
        <div class="alert alert-info">Không tìm thấy thông tin bảo hành tương ứng.</div>
    @endif

</div>
@endsection
