<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Choice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Choice>
 */
final class ChoiceFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Choice::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'question_id' => fake()->randomNumber(),
            'content' => fake()->optional()->text,
            'attachment' => fake()->optional()->word,
            'status ' => fake()->randomElement(['0', '1']),
        ];
    }
}
