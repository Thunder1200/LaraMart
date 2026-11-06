<?php

namespace Database\Factories;

use App\Enums\ReturnStatus;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReturnRequestFactory extends Factory
{
    protected $model = ReturnRequest::class;

    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'reason' => 'Beden uymadı, iade etmek istiyorum.',
            'status' => ReturnStatus::Pending,
            'refund_amount' => '250.00',
        ];
    }
}

