<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

class DestinationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'country' => fake()->country(),
            'region' => fake()->randomElement(['Africa', 'Asia', 'Europe', 'North America', 'Oceania', 'South America']),
            'cost_level' => fake()->randomElement(Destination::COST_LEVELS),
            'activities' => fake()->randomElements(['Hiking & Trekking', 'Cultural Tours', 'Photography', 'Beach & Relaxation', 'Wildlife Safari', 'Food & Wine Tasting'], 3),
            'average_daily_budget' => fake()->numberBetween(40, 500),
            'annual_visitors' => fake()->numberBetween(100000, 10000000),
        ];
    }
}
