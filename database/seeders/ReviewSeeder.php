<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = DB::table('users')->where('role', 'customer')->get();
        $products = DB::table('products')->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            throw new \Exception('Cần có users và products trước khi seed reviews.');
        }

        $comments = [
            5 => [
                'Sản phẩm tuyệt vời, đúng như mô tả!',
                'Chất lượng xuất sắc, giao hàng nhanh.',
                'Rất hài lòng, sẽ mua lại lần sau.',
            ],
            4 => [
                'Sản phẩm tốt, nhưng giá hơi cao.',
                'Chất lượng ổn, đóng gói cẩn thận.',
                'Tạm hài lòng, thiếu vài tính năng.',
            ],
            3 => [
                'Bình thường, không có gì đặc biệt.',
                'Tạm được, giao hàng hơi chậm.',
            ],
        ];

        DB::table('reviews')->truncate();

        foreach ($products as $product) {
            foreach ($users as $user) {
                $rating = collect([5, 5, 4, 4, 3])->random();
                $commentList = $comments[$rating];
                DB::table('reviews')->insert([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'comment' => $commentList[array_rand($commentList)],
                    'created_at' => $now->copy()->subDays(rand(1, 30)),
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
