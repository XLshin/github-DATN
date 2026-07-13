<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Xóa các danh mục, brand, sản phẩm NGOÀI 3 danh mục chính:
 * 1 - Điện thoại, 2 - Máy tính bảng, 3 - Phụ kiện
 * và 4 brand chính: Apple, Samsung, Xiaomi, OPPO
 */
class CleanupExtraCategoriesSeeder extends Seeder
{
    private array $keepCategoryIds = [1, 2, 3];
    private array $keepBrandNames  = ['Apple', 'Samsung', 'Xiaomi', 'OPPO'];

    public function run(): void
    {
        $keepBrandIds = DB::table('brands')
            ->whereIn('name', $this->keepBrandNames)
            ->pluck('id')
            ->toArray();

        // 1. Tìm product_group ids cần xóa
        $delGroupIds = DB::table('product_groups')
            ->whereNotIn('category_id', $this->keepCategoryIds)
            ->pluck('id')
            ->toArray();

        // 2. Tìm product ids cần xóa (ngoài category hoặc thuộc group sẽ xóa)
        $delProductIds = DB::table('products')
            ->where(function ($q) use ($delGroupIds) {
                $q->whereNotIn('category_id', $this->keepCategoryIds)
                  ->orWhereIn('product_group_id', $delGroupIds);
            })
            ->pluck('id')
            ->toArray();

        if (!empty($delProductIds)) {
            // Xóa variants + imeis + inventory trước
            $variantIds = DB::table('product_variants')
                ->whereIn('product_id', $delProductIds)
                ->pluck('id')
                ->toArray();

            if (!empty($variantIds)) {
                DB::table('imeis')->whereIn('product_variant_id', $variantIds)->delete();
                DB::table('inventory_transactions')->whereIn('product_variant_id', $variantIds)->delete();
                DB::table('product_variants')->whereIn('id', $variantIds)->delete();
            }

            DB::table('product_images')->whereIn('product_id', $delProductIds)->delete();
            DB::table('reviews')->whereIn('product_id', $delProductIds)->delete();
            DB::table('cart_items')->whereIn('product_id', $delProductIds)->delete();
            DB::table('products')->whereIn('id', $delProductIds)->delete();
        }

        // 3. Xóa product_specifications và product_images thuộc group
        if (!empty($delGroupIds)) {
            DB::table('product_specifications')->whereIn('product_group_id', $delGroupIds)->delete();
            DB::table('product_images')->whereIn('product_group_id', $delGroupIds)->delete();
            DB::table('product_groups')->whereIn('id', $delGroupIds)->delete();
        }

        // 4. Xóa brand_category pivot cho brand thừa và brand thừa
        $delBrandIds = DB::table('brands')
            ->whereNotIn('id', $keepBrandIds)
            ->pluck('id')
            ->toArray();

        if (!empty($delBrandIds)) {
            DB::table('brand_category')->whereIn('brand_id', $delBrandIds)->delete();
            DB::table('brands')->whereIn('id', $delBrandIds)->delete();
        }

        // 5. Xóa danh mục thừa
        $delCatIds = DB::table('categories')
            ->whereNotIn('id', $this->keepCategoryIds)
            ->pluck('id')
            ->toArray();

        if (!empty($delCatIds)) {
            DB::table('brand_category')->whereIn('category_id', $delCatIds)->delete();
            DB::table('categories')->whereIn('id', $delCatIds)->delete();
        }

        $this->command->info('Đã xóa ' . count($delProductIds) . ' sản phẩm, ' . count($delGroupIds) . ' nhóm, ' . count($delBrandIds) . ' brand thừa, ' . count($delCatIds) . ' danh mục thừa.');
    }
}
