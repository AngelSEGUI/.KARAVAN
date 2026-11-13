<?php

namespace Database\Factories;

use App\Enums\HousingType;
use App\Models\Festival;
use App\Models\Housing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Housing>
 */
class HousingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'festival_id' => Festival::factory(),
            'title' => 'Jardin calme près du site',
            'type' => HousingType::Tent,
            'city' => fake()->city(),
            'distance_km' => 2.5,
            'capacity' => 4,
            'price_per_night' => 15,
            'description' => fake()->sentence(),
            'amenities' => ['Douche', 'Parking'],
        ];
    }
}
