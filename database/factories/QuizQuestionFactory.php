<?php

namespace Database\Factories;

use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $wrongAnswersArray = [
                fake()->word(),
                fake()->word(),
            ];

        $wrongAnswersString = '{' . implode(',', $wrongAnswersArray) . '}';

        return [
            'question' => fake()->word(),
            'answer' => fake()->word(),
            'wrong_answers' => $wrongAnswersString
        ];
    }
}
