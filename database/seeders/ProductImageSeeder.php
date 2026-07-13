<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Tạo ảnh placeholder SVG cho tất cả sản phẩm và biến thể,
 * sau đó cập nhật DB (products.thumbnail, product_images, product_variants.image_path).
 *
 * Chạy: php artisan db:seed --class=ProductImageSeeder
 */
class ProductImageSeeder extends Seeder
{
    // Màu nền chính cho từng sản phẩm (slug => [bg, text, accent])
    private array $productColors = [
        'iphone-16-pro'          => ['bg' => '#1C1C1E', 'text' => '#F5F5F7', 'accent' => '#FFD60A'],
        'samsung-galaxy-s25'     => ['bg' => '#1428A0', 'text' => '#FFFFFF', 'accent' => '#5CE1E6'],
        'xiaomi-redmi-note-15'   => ['bg' => '#FF6900', 'text' => '#FFFFFF', 'accent' => '#FFD200'],
        'oppo-reno-12'           => ['bg' => '#0078D4', 'text' => '#FFFFFF', 'accent' => '#00C7B1'],
        'ipad-air-6'             => ['bg' => '#636366', 'text' => '#F5F5F7', 'accent' => '#30D158'],
        'samsung-galaxy-tab-s10' => ['bg' => '#1C1C1E', 'text' => '#FFFFFF', 'accent' => '#0A84FF'],
        'apple-airpods-pro-3'    => ['bg' => '#F5F5F7', 'text' => '#1D1D1F', 'accent' => '#0071E3'],
        'samsung-galaxy-buds-3'  => ['bg' => '#2C2C2E', 'text' => '#FFFFFF', 'accent' => '#32ADE6'],
        'xiaomi-65w-charger'     => ['bg' => '#FF3A30', 'text' => '#FFFFFF', 'accent' => '#FFD60A'],
    ];

    // Màu hex cho từng tên màu biến thể
    private array $variantColorMap = [
        'Titan Đen'   => '#2D2D2D',
        'Titan Trắng' => '#E0E0E0',
        'Titan Sa Mạc' => '#C19A6B',
        'Đen'         => '#1A1A1A',
        'Trắng'       => '#F0F0F0',
        'Bạc'         => '#A8A9AD',
        'Xanh'        => '#0066CC',
        'Xanh Lá'     => '#2E7D32',
        'Hồng'        => '#E91E8C',
        'Vàng'        => '#FFB300',
        'Đỏ'          => '#D50000',
        'Tím'         => '#6200EA',
        'Xám'         => '#9E9E9E',
        'Cam'         => '#F57C00',
    ];

    public function run(): void
    {
        $products = DB::table('products')->orderBy('id')->get();
        $variants = DB::table('product_variants')->orderBy('product_id')->get();

        $this->command->info('Tạo ảnh sản phẩm...');

        foreach ($products as $product) {
            $palette = $this->productColors[$product->slug]
                ?? ['bg' => '#374151', 'text' => '#F9FAFB', 'accent' => '#60A5FA'];

            // Tạo 3 ảnh SVG cho sản phẩm
            for ($i = 1; $i <= 3; $i++) {
                $filename = "products/{$product->slug}-{$i}.svg";
                $label    = match ($i) {
                    1 => 'Ảnh chính',
                    2 => 'Góc nhìn 2',
                    default => 'Chi tiết',
                };
                $svg = $this->makeProductSvg(
                    $product->name,
                    $label,
                    $palette['bg'],
                    $palette['text'],
                    $palette['accent'],
                    $i,
                );
                Storage::disk('public')->put($filename, $svg);
            }

            // Cập nhật thumbnail sản phẩm
            DB::table('products')->where('id', $product->id)
                ->update(['thumbnail' => "products/{$product->slug}-1.svg"]);

            // Xóa product_images cũ, thêm mới
            DB::table('product_images')->where('product_id', $product->id)->delete();
            for ($i = 1; $i <= 3; $i++) {
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'image_path' => "products/{$product->slug}-{$i}.svg",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->line("  ✓ {$product->name}");
        }

        $this->command->newLine();
        $this->command->info('Tạo ảnh biến thể...');

        foreach ($variants as $variant) {
            $product  = $products->firstWhere('id', $variant->product_id);
            $palette  = $this->productColors[$product->slug ?? '']
                ?? ['bg' => '#374151', 'text' => '#F9FAFB', 'accent' => '#60A5FA'];

            $colorHex = $this->variantColorMap[$variant->color] ?? $palette['accent'];
            $filename = "variants/variant-{$variant->id}.svg";

            $svg = $this->makeVariantSvg(
                $product->name ?? 'Sản phẩm',
                $variant->color,
                $variant->storage,
                $colorHex,
            );

            Storage::disk('public')->put($filename, $svg);

            DB::table('product_variants')->where('id', $variant->id)
                ->update(['image_path' => $filename]);

            $this->command->line("  ✓ Variant #{$variant->id}: {$variant->color} / {$variant->storage}");
        }

        $this->command->newLine();
        $this->command->info('Hoàn tất! Ảnh SVG đã lưu vào storage/app/public/');
    }

    /** Tạo SVG ảnh chính sản phẩm (400×400). */
    private function makeProductSvg(
        string $name,
        string $label,
        string $bg,
        string $textColor,
        string $accent,
        int $idx,
    ): string {
        $nameEscaped  = htmlspecialchars($name, ENT_XML1);
        $labelEscaped = htmlspecialchars($label, ENT_XML1);
        $cornerR      = ($idx === 1) ? 12 : ($idx === 2 ? 8 : 4);

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="400" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg{$idx}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bg}" stop-opacity="1"/>
      <stop offset="100%" stop-color="{$accent}" stop-opacity="0.35"/>
    </linearGradient>
    <filter id="sh">
      <feDropShadow dx="0" dy="4" stdDeviation="8" flood-color="rgba(0,0,0,0.35)"/>
    </filter>
  </defs>
  <rect width="400" height="400" fill="url(#bg{$idx})" rx="16"/>
  <rect x="0" y="0" width="400" height="6" fill="{$accent}" rx="0"/>
  <g transform="translate(140,75)" filter="url(#sh)">
    <rect width="120" height="185" rx="{$cornerR}" fill="{$textColor}" opacity="0.15"/>
    <rect x="8" y="14" width="104" height="153" rx="4" fill="{$textColor}" opacity="0.08"/>
    <circle cx="60" cy="176" r="6" fill="{$accent}" opacity="0.55"/>
    <rect x="40" y="7" width="40" height="4" rx="2" fill="{$textColor}" opacity="0.25"/>
  </g>
  <circle cx="340" cy="58" r="44" fill="{$accent}" opacity="0.12"/>
  <circle cx="56" cy="345" r="28" fill="{$accent}" opacity="0.09"/>
  <text x="200" y="302" font-family="system-ui,-apple-system,sans-serif" font-size="21" font-weight="700"
        text-anchor="middle" fill="{$textColor}" opacity="0.95">{$nameEscaped}</text>
  <text x="200" y="328" font-family="system-ui,-apple-system,sans-serif" font-size="13"
        text-anchor="middle" fill="{$accent}" opacity="0.85">{$labelEscaped}</text>
  <circle cx="370" cy="370" r="19" fill="{$accent}" opacity="0.65"/>
  <text x="370" y="376" font-family="system-ui,sans-serif" font-size="14" font-weight="700"
        text-anchor="middle" fill="{$bg}">{$idx}</text>
</svg>
SVG;
    }

    /** Tạo SVG ảnh biến thể (300×300). */
    private function makeVariantSvg(
        string $productName,
        string $color,
        string $storage,
        string $colorHex,
    ): string {
        $isDark      = $this->isDark($colorHex);
        $labelColor  = $isDark ? '#FFFFFF' : '#1A1A1A';
        $nameShort   = mb_strlen($productName) > 16 ? mb_substr($productName, 0, 14) . '…' : $productName;
        $nameEsc     = htmlspecialchars($nameShort, ENT_XML1);
        $colorEsc    = htmlspecialchars($color, ENT_XML1);
        $storageEsc  = htmlspecialchars($storage, ENT_XML1);
        $lighter     = $isDark
            ? $this->adjustBrightness($colorHex, 40)
            : $this->adjustBrightness($colorHex, -30);

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="300" height="300" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="vbg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$colorHex}" stop-opacity="1"/>
      <stop offset="100%" stop-color="{$lighter}" stop-opacity="1"/>
    </linearGradient>
    <filter id="vshadow">
      <feDropShadow dx="0" dy="3" stdDeviation="6" flood-color="rgba(0,0,0,0.4)"/>
    </filter>
  </defs>
  <rect width="300" height="300" fill="url(#vbg)" rx="12"/>
  <rect width="300" height="300" fill="none" rx="12" stroke="{$labelColor}" stroke-width="1.5" stroke-opacity="0.2"/>
  <g transform="translate(95,45)" filter="url(#vshadow)">
    <rect width="110" height="168" rx="10" fill="{$labelColor}" opacity="0.18"/>
    <rect x="7" y="12" width="96" height="140" rx="4" fill="{$labelColor}" opacity="0.10"/>
    <circle cx="55" cy="160" r="5" fill="{$labelColor}" opacity="0.45"/>
    <rect x="35" y="6" width="40" height="4" rx="2" fill="{$labelColor}" opacity="0.28"/>
  </g>
  <rect x="18" y="238" width="264" height="46" rx="8" fill="{$labelColor}" opacity="0.12"/>
  <text x="150" y="258" font-family="system-ui,-apple-system,sans-serif" font-size="13" font-weight="700"
        text-anchor="middle" fill="{$labelColor}" opacity="0.92">{$nameEsc}</text>
  <text x="150" y="276" font-family="system-ui,-apple-system,sans-serif" font-size="11"
        text-anchor="middle" fill="{$labelColor}" opacity="0.75">{$colorEsc} · {$storageEsc}</text>
  <circle cx="268" cy="32" r="15" fill="{$labelColor}" opacity="0.22"/>
  <circle cx="268" cy="32" r="9" fill="{$colorHex}" stroke="{$labelColor}" stroke-width="2" opacity="0.9"/>
</svg>
SVG;
    }

    /** Kiểm tra màu có tối không (luminance < 128). */
    private function isDark(string $hex): bool
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return (0.299 * hexdec(substr($hex, 0, 2))
            + 0.587 * hexdec(substr($hex, 2, 2))
            + 0.114 * hexdec(substr($hex, 4, 2))) < 128;
    }

    /** Làm sáng/tối màu hex theo delta (-255..255). */
    private function adjustBrightness(string $hex, int $delta): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $delta));
        $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $delta));
        $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $delta));
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
