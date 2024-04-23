<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TrueFalseQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TrueFalseQuestion>
 */
final class TrueFalseQuestionFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = TrueFalseQuestion::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'question_id' => fake()->randomNumber(),
            'answer' => fake()->randomElement(['0', '1']),
        ];
    }
}
