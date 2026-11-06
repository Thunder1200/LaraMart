<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('KUPON###')),
            'type' => CouponType::Percentage,
            'value' => '10.00',
            'min_total' => '200.00',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => 0,
        ];
    }
}
