<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        $storeName = fake()->unique()->company().' Mağaza';

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => Str::slug($storeName).'-'.fake()->unique()->numberBetween(100, 999),
            'tax_number' => fake()->unique()->numerify('##########'),
            'iban' => 'TR'.fake()->numerify('########################'),
            'status' => 'approved',
            'commission_rate' => null,
        ];
    }
}
