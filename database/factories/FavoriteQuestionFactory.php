<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FavoriteQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FavoriteQuestion>
 */
final class FavoriteQuestionFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = FavoriteQuestion::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'user_id' => fake()->uuid,
            'question_id' => fake()->randomNumber(),
            'combination_id' => fake()->optional()->randomNumber(),
        ];
    }
}
