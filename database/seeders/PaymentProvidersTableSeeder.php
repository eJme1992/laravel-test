<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentProvider;

class PaymentProvidersTableSeeder extends Seeder
{
    public function run()
    {
        PaymentProvider::insert([
            [
                'name' => 'easymoney',
                'base_url' => 'http://localhost:3001',
                'api_key' => null,
                'callback_url' => null,
                'supports_webhook' => false,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'superwalletz',
                'base_url' => 'http://localhost:3002',
                'api_key' => null,
                'callback_url' => 'http://127.0.0.1:8000/webhooks/superwalletz',
                'supports_webhook' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
