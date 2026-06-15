<?php

namespace Database\Factories;

use App\Models\Poll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poll>
 */
class PollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $variantsArray = [
                fake()->word(),
                fake()->word(),
            ];

        $variantsString = '{' . implode(',', $variantsArray) . '}';
        return [
            'title' => fake()->name(),
            'variants' => $variantsString,
        ];
    }
}