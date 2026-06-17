<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'phone' => '0900000000',
                'address' => 'Hà Nội',
                'role' => 'admin',
                'total_spent' => 0,
                'membership_level' => 'gold',
            ]
        );
    }
}
