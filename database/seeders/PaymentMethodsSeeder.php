<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'online_transfer',
                'name' => 'Online Transfer',
                'sort_order' => 10,
            ],
            [
                'code' => 'direct_deposit',
                'name' => 'Direct Bank Deposit',
                'sort_order' => 20,
            ],
            [
                'code' => 'crypto',
                'name' => 'Crypto',
                'sort_order' => 30,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method + ['customer_portal' => false, 'status' => false],
            );
        }
    }
}
