<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Seller;
use App\Models\SubOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubOrder>
 */
class SubOrderFactory extends Factory
{
    protected $model = SubOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 150, 1500);

        return [
            'order_id' => Order::factory(),
            'seller_id' => Seller::factory(),
            'status' => 'pending_approval',
            'cargo_company' => null,
            'tracking_number' => null,
            'subtotal' => $subtotal,
            'shipping_amount' => $subtotal >= 300 ? 0.00 : 49.90,
            'commission_amount' => round($subtotal * 0.10, 2),
        ];
    }
}
