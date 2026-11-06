<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'image' => 'https://placehold.co/1200x400/1e293b/ffffff?text=LaraMart+Kampanya',
            'url' => '/',
            'sort' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}

