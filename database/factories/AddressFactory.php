<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Ev Adresim', 'İş Adresim', 'Yazlık']),
            'city' => fake()->randomElement(['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya']),
            'district' => fake()->randomElement(['Kadıköy', 'Çankaya', 'Karşıyaka', 'Nilüfer', 'Muratpaşa']),
            'full_address' => fake()->streetAddress().' No: '.fake()->buildingNumber(),
            'is_default' => false,
        ];
    }
}
