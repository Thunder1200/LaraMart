<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed']);

        return [
            'code' => strtoupper(fake()->unique()->bothify('KUPON###')),
            'type' => $type,
            'value' => $type === 'percentage' ? fake()->randomElement([10, 15, 20]) : fake()->randomElement([50, 100, 150]),
            'min_total' => fake()->randomElement([200, 300, 500]),
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => 0,
        ];
    }
}
