<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'image' => 'banners/sample-banner.jpg',
            'url' => '/kategori/elektronik',
            'sort' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
