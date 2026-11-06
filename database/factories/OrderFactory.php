<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'LM-'.strtoupper(Str::random(10)),
            'idempotency_key' => (string) Str::uuid(),
            'address' => [
                'title' => 'Ev Adresim',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'full_address' => 'Moda Caddesi No: 10',
            ],
            'subtotal' => '500.00',
            'discount' => '0.00',
            'shipping' => '0.00',
            'total' => '500.00',
            'payment_status' => PaymentStatus::Paid,
            'coupon_code' => null,
        ];
    }
}
