<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\SellerPayout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SellerPayout>
 */
class SellerPayoutFactory extends Factory
{
    protected $model = SellerPayout::class;

    public function definition(): array
    {
        $gross = fake()->randomFloat(2, 5000, 50000);
        $commission = round($gross * 0.12, 2);

        return [
            'seller_id' => Seller::factory(),
            'period' => now()->format('Y-m'),
            'gross' => $gross,
            'commission' => $commission,
            'net' => round($gross - $commission, 2),
            'paid_at' => null,
        ];
    }
}
