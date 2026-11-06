<?php

namespace Database\Factories;

use App\Enums\SellerStatus;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        $storeName = fake()->unique()->company().' Mağazası';

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => Str::slug($storeName).'-'.fake()->unique()->numerify('###'),
            'tax_number' => fake()->numerify('##########'),
            'iban' => 'TR'.fake()->numerify('########################'),
            'status' => SellerStatus::Approved,
            'commission_rate' => fake()->randomElement(['8.00', '10.00', '12.00', '15.00']),
        ];
    }
}
