<?php

namespace Database\Factories;

use App\Enums\SubOrderStatus;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SubOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubOrderFactory extends Factory
{
    protected $model = SubOrder::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'seller_id' => Seller::factory(),
            'status' => SubOrderStatus::PendingApproval,
            'cargo_company' => null,
            'tracking_number' => null,
            'subtotal' => '500.00',
            'shipping' => '0.00',
            'commission_amount' => '50.00',
        ];
    }
}
