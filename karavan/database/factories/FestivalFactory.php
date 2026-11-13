<?php

namespace Database\Factories;

use App\Models\Festival;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Festival>
 */
class FestivalFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 month', '+8 months');
        $name = 'Festival ' . fake()->unique()->word() . ' ' . fake()->numberBetween(1, 999);

        return [
            'name' => $name,
            'city' => fake()->city(),
            'region' => fake()->randomElement(['Bretagne', 'Île-de-France', 'Occitanie']),
            'venue' => 'Parc ' . fake()->lastName(),
            'starts_on' => $start,
            'ends_on' => (clone $start)->modify('+2 days'),
            'genres' => ['Rock', 'Pop'],
            'lineup' => ['Artiste A', 'Artiste B'],
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'attendance' => fake()->numberBetween(10000, 200000),
            'price_from' => fake()->numberBetween(30, 120),
            'hue' => fake()->numberBetween(0, 359),
            'is_featured' => false,
        ];
    }
}
