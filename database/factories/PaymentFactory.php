<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'iyzico',
            'transaction_id' => 'IYZ-'.strtoupper(Str::random(12)),
            'amount' => fake()->randomFloat(2, 100, 3000),
            'status' => 'success',
            'raw_response' => ['status' => 'success'],
        ];
    }
}
