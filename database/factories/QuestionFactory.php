<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Question>
 */
final class QuestionFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Question::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'content' => fake()->text,
            'attachment' => fake()->optional()->word,
            'title' => fake()->optional()->title,
            'type' => fake()->randomElement(['0', '1']),
            'difficulty_level' => fake()->randomFloat(),
            'status' => fake()->randomElement(['0', '1', '2', '3']),
            'accessability_status' => fake()->randomElement(['0', '1', '2']),
            'estimated_answer_time' => fake()->randomNumber(),
            'language' => fake()->randomElement(['0', '1']),
            'topic_id' => fake()->randomNumber(),
        ];
    }
}
