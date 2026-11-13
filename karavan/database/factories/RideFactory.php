<?php

namespace Database\Factories;

use App\Models\Festival;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ride>
 */
class RideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'festival_id' => Festival::factory(),
            'departure_city' => fake()->city(),
            'meeting_point' => 'Gare centrale',
            'departs_at' => now()->addMonths(2)->setTime(9, 0),
            'returns_at' => now()->addMonths(2)->addDays(3)->setTime(18, 0),
            'seats' => 3,
            'price' => 20,
            'vehicle' => 'Clio',
            'notes' => null,
        ];
    }
}
