<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\SellerPayout;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerPayoutFactory extends Factory
{
    protected $model = SellerPayout::class;

    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'period' => now()->format('Y-m'),
            'gross' => '1000.00',
            'commission' => '100.00',
            'net' => '900.00',
            'paid_at' => now(),
        ];
    }
}
