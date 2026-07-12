@extends('layouts.client')

@section('title', 'Chi tiết phiếu bảo hành')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">Chi tiết phiếu bảo hành</h1>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Mã phiếu: {{ $warranty->warranty_code }}</h5>
            <p class="mb-1">Trạng thái: <span class="badge text-bg-{{ $warranty->status_badge }}">{{ $warranty->status_label }}</span></p>
            <p class="mb-1">IMEI: {{ $warranty->imei?->imei ?? '-' }}</p>
            <p class="mb-1">Sản phẩm: {{ $warranty->imei?->productVariant?->product?->name ?? '-' }}</p>
            <p class="mb-1">Ngày bắt đầu bảo hành: {{ $warranty->warranty_start?->format('d/m/Y') ?? '-' }}</p>
            <p class="mb-1">Hạn bảo hành: {{ $warranty->warranty_end?->format('d/m/Y') ?? '-' }}</p>
            <p class="mb-1">Đơn hàng: {{ $warranty->order?->order_code ?? '-' }}</p>
            <p class="mb-1">Khách hàng: {{ $warranty->order?->customer_name ?? '-' }} - {{ $warranty->order?->customer_phone ?? '-' }}</p>
            <hr>
            <h6>Ghi chú khách hàng</h6>
            <p>{{ $warranty->customer_note ?? '-' }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Lịch sử & trạng thái xử lý</h5>

            <p><strong>Ý nghĩa trạng thái:</strong></p>
            <ul>
                <li><strong>Đang xử lý bảo hành</strong>: Phiếu đang được tiếp nhận và kỹ thuật xử lý.</li>
                <li><strong>Hoàn tất xử lý</strong>: Phiếu đã xử lý xong; IMEI có thể trở lại trạng thái bán hoặc tiếp tục được tạo phiếu mới nếu còn hạn bảo hành.</li>
            </ul>

            @if(!empty($histories))
                <div class="timeline">
                    @foreach($histories as $h)
                        <div class="mb-3">
                            <div class="small text-muted">{{ \Illuminate\Support\Carbon::parse($h['time'])->format('d/m/Y H:i') }}</div>
                            <div><strong>{{ $h['title'] }}</strong></div>
                            <div class="text-muted">{{ $h['description'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted">Chưa có lịch sử nào.</div>
            @endif
        </div>
    </div>

    <a href="{{ route('warranties.lookup') }}" class="btn btn-light">← Quay lại tra cứu</a>
</div>
@endsection
