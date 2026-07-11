@extends('layouts.admin')

@section('title', 'Chi tiết IMEI')

@section('page_icon', 'bi-upc-scan')
@section('page_eyebrow', 'Kho hàng')
@section('page_title', 'Chi tiết IMEI')
@section('page_subtitle', 'Thông tin chi tiết thiết bị theo IMEI')

@section('heading_actions')
<a href="{{ route('admin.imeis.edit', $imei->id) }}"
   class="btn btn-primary btn-sm">
    <i class="bi bi-sliders"></i>
    Điều chỉnh
</a>
<a href="{{ route('admin.stocks') }}"
   class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left"></i>
    Quay lại
</a>
@endsection

@section('content')

<div class="row g-3">

    {{-- Thông tin thiết bị --}}
    <div class="col-lg-6">

        <section class="panel">

            <div class="panel-header">
                <h5 class="mb-0">
                    Thông tin thiết bị
                </h5>
            </div>

            <div class="p-3">

                <table class="table align-middle mb-0">

                    <tr>
                        <th width="180">IMEI/Serial</th>
                        <td>
                            <strong>{{ $imei->imei }}</strong>
                        </td>
                    </tr>

                    <tr>
                        <th>Sản phẩm</th>
                        <td>
                            {{ $imei->productVariant?->product?->name }}
                        </td>
                    </tr>

                    <tr>
                        <th>Màu sắc</th>
                        <td>
                            {{ $imei->productVariant?->color }}
                        </td>
                    </tr>

                    <tr>
                        <th>Dung lượng</th>
                        <td>
                            {{ $imei->productVariant?->storage }}
                        </td>
                    </tr>

                    <tr>
                        <th>Thương hiệu</th>
                        <td>
                            {{ $imei->productVariant?->product?->brand?->name ?? '--' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Ngày nhập kho</th>
                        <td>
                            {{ $imei->created_at?->format('d/m/Y H:i') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Trạng thái</th>

                        <td>

                            @switch($imei->status)

                                @case('available')
                                    <span class="badge text-bg-success">
                                        Còn hàng
                                    </span>
                                    @break
                                @case('reserved')
                                    <span class="badge text-bg-success">
                                        Tạm giữ
                                    </span>
                                    @break

                                @case('sold')
                                    <span class="badge text-bg-primary">
                                        Đã bán
                                    </span>
                                    @break

                                @case('warranty')
                                    <span class="badge text-bg-warning">
                                        Đang bảo hành
                                    </span>
                                    @break

                                @case('returned')
                                    <span class="badge text-bg-secondary">
                                        Đã trả hàng
                                    </span>
                                    @break

                            @endswitch

                        </td>
                    </tr>

                </table>

            </div>

        </section>

    </div>

    {{-- Thông tin bán hàng --}}
    <div class="col-lg-6">

        <section class="panel">

            <div class="panel-header">
                <h5 class="mb-0">
                    Thông tin bán hàng
                </h5>
            </div>

            <div class="p-3">

                <table class="table align-middle mb-0">

<tr>
    <th width="180">Mã đơn hàng</th>
    <td>
        {{ $imei->orderItem?->order?->order_code ?? '--' }}
    </td>
</tr>

<tr>
    <th>Khách hàng</th>
    <td>
        {{ $imei->orderItem?->order?->user?->name ?? '--' }}
    </td>
</tr>

<tr>
    <th>Email</th>
    <td>
        {{ $imei->orderItem?->order?->user?->email ?? '--' }}
    </td>
</tr>

<tr>
    <th>Ngày bán</th>
    <td>
        {{
            $imei->orderItem?->order?->created_at
            ? $imei->orderItem?->order?->created_at->format('d/m/Y H:i')
            : '--'
        }}
    </td>
</tr>

                </table>

            </div>

        </section>

    </div>

    {{-- Bảo hành --}}
    <div class="col-12">

        <section class="panel">

            <div class="panel-header">
                <h5 class="mb-0">
                    Thông tin bảo hành
                </h5>
            </div>

            <div class="p-3">

 <table class="table align-middle mb-0">
@php
    $warranty = $imei->warranty;
@endphp
<tr>
    <th width="220">
        Trạng thái bảo hành
    </th>

    <td>

        @if(!$warranty)

            <span class="badge text-bg-secondary">
                Chưa kích hoạt
            </span>

        @elseif(now()->lt($warranty->warranty_end))

            <span class="badge text-bg-success">
                Còn bảo hành
            </span>

        @else

            <span class="badge text-bg-danger">
                Hết bảo hành
            </span>

        @endif

    </td>
</tr>

<tr>
    <th>Ngày kích hoạt bảo hành</th>

    <td>
        {{ $warranty?->warranty_start?->format('d/m/Y') ?? '--' }}
    </td>
</tr>

<tr>
    <th>Hạn bảo hành</th>

    <td>
        {{ $warranty?->warranty_end?->format('d/m/Y') ?? '--' }}
    </td>
</tr>

<tr>
    <th>Thời gian còn lại</th>

    <td>

        @if(
            $warranty &&
            now()->lt($warranty->warranty_end)
        )

            {{ floor(now()->diffInDays($warranty->warranty_end)) }}
            ngày

        @else

            --

        @endif

    </td>
</tr>

</table>

            </div>

        </section>

    </div>



</div>

@endsection
