<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Tự vẽ một ảnh "hóa đơn điện tử" (PNG) cho các giao dịch được hệ thống tự động xác nhận
 * (quét QR momo/vnpay/chuyển khoản, không có ảnh chụp màn hình do khách gửi) — dùng làm
 * chứng từ lưu trữ bên admin, giống vai trò của proof_image cho các giao dịch đối soát thủ công.
 *
 * Chỉ dùng font bitmap có sẵn của GD (không phụ thuộc file TTF nào), nên nội dung tiếng Việt
 * được ghi không dấu để tránh hiển thị lỗi ký tự.
 */
class ReceiptImageService
{
    /**
     * @param  array{r:int,g:int,b:int}  $color  Màu thương hiệu (momo/vnpay/ngân hàng...)
     * @param  array<int, array{0:string,1:string}>  $rows  Danh sách [nhãn, giá trị]
     */
    public function generate(string $title, string $amountText, array $color, array $rows): string
    {
        $width = 480;
        $headerHeight = 110;
        $rowHeight = 34;
        $height = $headerHeight + 70 + (count($rows) * $rowHeight) + 30;

        $image = imagecreatetruecolor($width, $height);

        $brand = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);
        $white = imagecolorallocate($image, 255, 255, 255);
        $bodyBg = imagecolorallocate($image, 250, 250, 252);
        $label = imagecolorallocate($image, 130, 130, 145);
        $value = imagecolorallocate($image, 30, 30, 35);
        $line = imagecolorallocate($image, 230, 230, 235);

        imagefilledrectangle($image, 0, 0, $width, $height, $bodyBg);
        imagefilledrectangle($image, 0, 0, $width, $headerHeight, $brand);

        // Vòng tròn trắng + dấu tích
        $cx = (int) ($width / 2);
        $cy = 50;
        imagefilledellipse($image, $cx, $cy, 56, 56, $white);
        imagesetthickness($image, 4);
        imageline($image, $cx - 16, $cy, $cx - 4, $cy + 12, $brand);
        imageline($image, $cx - 4, $cy + 12, $cx + 18, $cy - 12, $brand);
        imagesetthickness($image, 1);

        $this->centerText($image, $title, $cx, 92, 4, $white);

        // Số tiền — vẽ lặp lại lệch 1px để giả hiệu ứng đậm
        $this->centerText($image, $amountText, $cx, $headerHeight + 30, 5, $brand);
        $this->centerText($image, $amountText, $cx, $headerHeight + 30, 5, $brand, 1);

        $y = $headerHeight + 60;
        foreach ($rows as [$rowLabel, $rowValue]) {
            imagestring($image, 4, 20, $y, $rowLabel, $label);
            $valueWidth = imagefontwidth(4) * strlen($rowValue);
            imagestring($image, 4, $width - 20 - $valueWidth, $y, $rowValue, $value);
            imageline($image, 20, $y + 24, $width - 20, $y + 24, $line);
            $y += $rowHeight;
        }

        $path = 'receipts/' . now()->format('Ymd') . '/' . Str::random(20) . '.png';
        $fullPath = Storage::disk('public')->path($path);
        Storage::disk('public')->makeDirectory(dirname($path));

        imagepng($image, $fullPath);
        imagedestroy($image);

        return $path;
    }

    private function centerText($image, string $text, int $centerX, int $y, int $font, int $color, int $offsetX = 0): void
    {
        $textWidth = imagefontwidth($font) * strlen($text);
        imagestring($image, $font, $centerX - (int) ($textWidth / 2) + $offsetX, $y, $text, $color);
    }
}
