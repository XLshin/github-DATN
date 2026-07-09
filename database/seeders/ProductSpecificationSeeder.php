<?php

namespace Database\Seeders;

use App\Models\ProductGroup;
use Illuminate\Database\Seeder;

class ProductSpecificationSeeder extends Seeder
{
    public function run(): void
    {
        ProductGroup::query()
            ->orderBy('id')
            ->get()
            ->each(function (ProductGroup $productGroup) {
                $productGroup->specifications()->delete();

                $specifications = $this->specificationsFor($productGroup);

                foreach ($specifications as $index => $specification) {
                    $productGroup->specifications()->create([
                        'group_name' => $specification[0],
                        'name' => $specification[1],
                        'value' => $specification[2],
                        'sort_order' => $index,
                    ]);
                }
            });
    }

    private function specificationsFor(ProductGroup $productGroup): array
    {
        $name = mb_strtolower($productGroup->name);

        if ($productGroup->product_type === 'imei/serial') {
            return $this->phoneSpecifications($name);
        }

        if (str_contains($name, 'sạc') || str_contains($name, 'sac') || str_contains($name, 'charger')) {
            return $this->chargerSpecifications($productGroup);
        }

        return $this->accessorySpecifications($productGroup);
    }

    private function phoneSpecifications(string $name): array
    {
        $isProMax = str_contains($name, 'pro max');
        $isPro = str_contains($name, 'pro');

        return [
            ['Màn hình', 'Kích thước', $isProMax ? '6.9 inch' : '6.3 inch'],
            ['Màn hình', 'Công nghệ', 'OLED Super Retina XDR'],
            ['Màn hình', 'Tần số quét', $isPro ? '120Hz ProMotion' : '60Hz'],
            ['Hiệu năng', 'Chip', $isPro ? 'Apple A19 Pro' : 'Apple A19'],
            ['Hiệu năng', 'RAM', $isProMax ? '12GB' : '8GB'],
            ['Camera', 'Camera sau', $isPro ? '48MP + 48MP + 12MP' : '48MP + 12MP'],
            ['Camera', 'Camera trước', '12MP TrueDepth'],
            ['Pin & sạc', 'Dung lượng pin', $isProMax ? '4700 mAh' : '3600 mAh'],
            ['Pin & sạc', 'Sạc nhanh', 'Hỗ trợ sạc nhanh 30W'],
            ['Kết nối', 'SIM', 'Nano SIM + eSIM'],
            ['Kết nối', 'Mạng di động', '5G'],
            ['Hệ điều hành', 'Phiên bản', 'iOS 26'],
            ['Bảo hành', 'Thời gian bảo hành', '12 tháng chính hãng'],
        ];
    }

    private function chargerSpecifications(ProductGroup $productGroup): array
    {
        return [
            ['Thông tin chung', 'Dòng sản phẩm', $productGroup->name],
            ['Sạc', 'Công suất tối đa', '30W'],
            ['Sạc', 'Cổng sạc', 'USB-C'],
            ['Sạc', 'Chuẩn sạc', 'Power Delivery, Quick Charge'],
            ['Sạc', 'Điện áp đầu vào', '100-240V'],
            ['Sạc', 'Điện áp đầu ra', '5V/3A, 9V/3A, 12V/2.5A'],
            ['Thiết kế', 'Chất liệu', 'Nhựa PC chống cháy'],
            ['Thiết kế', 'Màu sắc', 'Trắng'],
            ['Tương thích', 'Thiết bị hỗ trợ', 'Điện thoại, máy tính bảng, tai nghe, đồng hồ thông minh'],
            ['Bảo hành', 'Thời gian bảo hành', '12 tháng'],
        ];
    }

    private function accessorySpecifications(ProductGroup $productGroup): array
    {
        return [
            ['Thông tin chung', 'Dòng sản phẩm', $productGroup->name],
            ['Thông tin chung', 'Loại quản lý', 'Theo số lượng'],
            ['Thiết kế', 'Chất liệu', 'Nhựa / silicone / kim loại tùy phiên bản'],
            ['Thiết kế', 'Màu sắc', 'Tùy biến thể sản phẩm'],
            ['Tương thích', 'Thiết bị hỗ trợ', 'Điện thoại, máy tính bảng và phụ kiện tương thích'],
            ['Bảo hành', 'Thời gian bảo hành', '6-12 tháng tùy sản phẩm'],
        ];
    }
}
