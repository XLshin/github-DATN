<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu giao hàng {{ $order->order_code }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #111;
        }

        .print-button {
            text-align: center;
            margin: 20px;
        }

        .print-button button {
            padding: 10px 18px;
            font-size: 14px;
            cursor: pointer;
        }

        .label {
            width: 760px;
            margin: 0 auto;
            border: 2px solid #111;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #111;
            padding: 8px;
            vertical-align: top;
        }

        th {
            text-align: left;
            background: #f2f2f2;
        }

        .no-border td {
            border: none;
            padding: 4px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .small {
            font-size: 12px;
            color: #555;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                margin: 0;
            }

            .label {
                width: auto;
                margin: 0;
                border: 2px solid #111;
            }
        }
    </style>
</head>

<body>
@php
    $receiverName = $order->receiver->receiver_name
        ?? $order->customer_name
        ?? $order->user->name
        ?? 'Guest';

    $receiverPhone = $order->receiver->receiver_phone
        ?? $order->customer_phone
        ?? '-';

    $receiverAddress = $order->receiver->receiver_address
        ?? $order->shipping_address
        ?? '-';

    $receiverNote = $order->receiver->receiver_note ?? null;

    $paymentStatus = $order->payment->payment_status ?? null;

    $paymentLabels = [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn tiền',
    ];
@endphp

<div class="print-button">
    <button onclick="window.print()">In phiếu giao hàng</button>
</div>

<div class="label">
    <h2>PHIẾU GIAO HÀNG NỘI BỘ</h2>

    <table class="no-border">
        <tr>
            <td>
                <strong>Mã đơn:</strong> {{ $order->order_code }}
            </td>

            <td>
                <strong>Ngày đặt:</strong> {{ $order->created_at?->format('d/m/Y H:i') }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Người nhận:</strong> {{ $receiverName }}
            </td>

            <td>
                <strong>SĐT người nhận:</strong> {{ $receiverPhone }}
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <strong>Địa chỉ nhận hàng:</strong> {{ $receiverAddress }}
            </td>
        </tr>

        @if($receiverNote)
            <tr>
                <td colspan="2">
                    <strong>Ghi chú người nhận:</strong> {{ $receiverNote }}
                </td>
            </tr>
        @endif

        <tr>
            <td>
                <strong>Khách hàng đặt:</strong>
                {{ $order->customer_name ?? $order->user->name ?? 'Guest' }}
            </td>

            <td>
                <strong>SĐT khách hàng:</strong>
                {{ $order->customer_phone ?? '-' }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Tổng tiền:</strong>
                {{ number_format($order->total_amount, 0, ',', '.') }} đ
            </td>

            <td>
                <strong>Thanh toán:</strong>
                {{ $paymentLabels[$paymentStatus] ?? 'Chưa có' }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Phương thức:</strong>
                {{ strtoupper($order->payment->payment_method ?? '-') }}
            </td>

            <td>
                <strong>Ngày in:</strong>
                {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 34%;">Sản phẩm</th>
                <th style="width: 22%;">Biến thể</th>
                <th style="width: 8%;" class="text-center">SL</th>
                <th style="width: 8%;" class="text-right">Tiền</th>
            </tr>
        </thead>

        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product->name ?? ('Product #' . $item->product_id) }}</strong>
                    </td>

                    <td>
                        @if($item->variant)
                            <strong>{{ $item->variant->color ?? '-' }}</strong>

                            @if(!empty($item->product?->storage))
                                <br>
                                <span class="small">
                                    {{ $item->product->storage }}
                                </span>
                            @endif
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-center">
                        {{ $item->quantity }}
                    </td>

                    <td class="text-right">
                        {{ number_format($item->total, 0, ',', '.') }} đ
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        <strong>Ghi chú giao hàng:</strong>
        ........................................................................................
    </p>

    <table class="no-border" style="margin-top: 30px;">
        <tr>
            <td style="text-align:center;">
                <strong>Người giao hàng</strong>
                <br><br><br>
                ..........................
            </td>

            <td style="text-align:center;">
                <strong>Người nhận hàng</strong>
                <br><br><br>
                ..........................
            </td>
        </tr>
    </table>
</div>
</body>
</html>
