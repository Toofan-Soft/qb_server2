<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuestionChoicesCombination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\QuestionChoicesCombination>
 */
final class QuestionChoicesCombinationFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = QuestionChoicesCombination::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'combination_id' => fake()->randomNumber(),
            'combination_choices' => fake()->word,
            'question_id' => fake()->randomNumber(),
        ];
    }
}
