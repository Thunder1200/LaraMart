<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnRequest>
 */
class ReturnRequestFactory extends Factory
{
    protected $model = ReturnRequest::class;

    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'reason' => 'Ürün beden/beklenti uyumsuzluğu nedeniyle iade talep edildi.',
            'status' => 'pending',
            'refund_amount' => fake()->randomFloat(2, 100, 1000),
        ];
    }
}
