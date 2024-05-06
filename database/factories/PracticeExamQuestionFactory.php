<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PracticeExamQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PracticeExamQuestion>
 */
final class PracticeExamQuestionFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = PracticeExamQuestion::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'practice_exam_id' => fake()->randomNumber(),
            'question_id' => fake()->randomNumber(),
            'combination_id' => fake()->optional()->randomNumber(),
            'answer' => fake()->optional()->randomNumber(),
            'answer_duration' => fake()->optional()->randomNumber(),
        ];
    }
}
