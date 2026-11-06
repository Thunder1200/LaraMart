<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'iyzico',
            'transaction_id' => 'TRX-' . fake()->unique()->numerify('########'),
            'amount' => '500.00',
            'status' => PaymentStatus::Paid,
            'raw_response' => ['status' => 'success', 'sandbox' => true],
        ];
    }
}

