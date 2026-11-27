<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\SubOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'sub_order_id' => SubOrder::factory(),
            'variant_id' => ProductVariant::factory(),
            'product_name' => fake()->words(3, true),
            'price' => fake()->randomFloat(2, 100, 1500),
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
