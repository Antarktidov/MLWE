<?php

namespace Database\Factories;

use App\Models\Medal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medal>
 */
class MedalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'image' => fake()->name(),
            'description' => fake()->name(),
            'wiki_id' => 0,
        ];
    }
}
