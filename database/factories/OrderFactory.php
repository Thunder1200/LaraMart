<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 200, 3000);
        $shipping = $subtotal >= 300 ? 0.00 : 49.90;

        return [
            'user_id' => User::factory(),
            'coupon_id' => null,
            'order_number' => 'LM-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'idempotency_key' => (string) Str::uuid(),
            'address' => [
                'title' => 'Ev',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'full_address' => fake()->streetAddress(),
            ],
            'subtotal' => $subtotal,
            'discount' => 0.00,
            'shipping' => $shipping,
            'total' => round($subtotal + $shipping, 2),
            'payment_status' => 'paid',
        ];
    }
}
