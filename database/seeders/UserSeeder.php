<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'phone' => '0900000000',
                'address' => 'Hà Nội',
                'email_verified_at' => $now,
                'password' => Hash::make('12345678'),
                'points' => 0,
                'role' => 'admin',
                'total_spent' => 0,
                'membership_level' => 'gold',
                'remember_token' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'customer.test@gmail.com'],
            [
                'name' => 'Khách hàng test',
                'phone' => '0987654321',
                'address' => 'Số 1 Cầu Giấy, Hà Nội',
                'email_verified_at' => $now,
                'password' => Hash::make('12345678'),
                'points' => 0,
                'role' => 'customer',
                'total_spent' => 0,
                'membership_level' => 'bronze',
                'remember_token' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
