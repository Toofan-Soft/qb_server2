<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuestionUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\QuestionUsage>
 */
final class QuestionUsageFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = QuestionUsage::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'question_id' => fake()->randomNumber(),
            'online_exam_last_selection_datetime' => fake()->optional()->randomNumber(),
            'practice_exam_last_selection_datetime' => fake()->optional()->randomNumber(),
            'paper_exam_last_selection_datetime' => fake()->optional()->randomNumber(),
            'online_exam_selection_times_count' => fake()->optional()->dateTime(),
            'practice_exam_selection_times_count' => fake()->optional()->dateTime(),
            'paper_exam_selection_times_count' => fake()->optional()->dateTime(),
            'online_exam_correct_answers_count' => fake()->optional()->randomNumber(),
            'online_exam_incorrect_answers_count' => fake()->optional()->randomNumber(),
            'practice_exam_incorrect_answers_count' => fake()->optional()->randomNumber(),
            'practice_exam_correct_answers_count' => fake()->optional()->randomNumber(),
        ];
    }
}
