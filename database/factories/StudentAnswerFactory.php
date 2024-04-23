<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StudentAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StudentAnswer>
 */
final class StudentAnswerFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = StudentAnswer::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'student_id' => fake()->randomNumber(),
            'question_id' => fake()->randomNumber(),
            'form_id' => fake()->randomNumber(),
            'answer' => fake()->optional()->randomNumber(),
            'answer_duration' => fake()->optional()->randomNumber(),
        ];
    }
}
