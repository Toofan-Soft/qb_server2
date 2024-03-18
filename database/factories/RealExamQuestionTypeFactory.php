<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RealExamQuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RealExamQuestionType>
 */
final class RealExamQuestionTypeFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = RealExamQuestionType::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'real_exam_id' => fake()->randomNumber(),
            'question_type' => fake()->randomElement(['0', '1']),
            'question_count' => fake()->randomNumber(),
            'question_score' => fake()->randomFloat(),
        ];
    }
}
