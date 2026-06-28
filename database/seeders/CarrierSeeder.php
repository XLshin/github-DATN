<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrier;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        // Create a simple local carrier used for development and automatic selection
        Carrier::updateOrCreate([
            'code' => 'local',
        ], [
            'name' => 'Local Carrier',
            'webhook_secret' => null,
            'active' => true,
            'api_credentials' => [],
        ]);
    }
}
