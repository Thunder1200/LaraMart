<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $cities = [
            'İstanbul' => ['Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Bakırköy'],
            'Ankara' => ['Çankaya', 'Yenimahalle', 'Keçiören', 'Etimesgut'],
            'İzmir' => ['Konak', 'Karşıyaka', 'Bornova', 'Buca'],
            'Bursa' => ['Nilüfer', 'Osmangazi', 'Yıldırım'],
            'Antalya' => ['Muratpaşa', 'Konyaaltı', 'Kepez'],
        ];

        $city = fake()->randomElement(array_keys($cities));
        $district = fake()->randomElement($cities[$city]);

        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Ev', 'İş', 'Aile Evi']),
            'city' => $city,
            'district' => $district,
            'full_address' => fake()->streetAddress().' '.$district.'/'.$city,
            'is_default' => false,
        ];
    }
}
