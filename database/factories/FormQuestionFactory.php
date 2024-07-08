<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FormQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FormQuestion>
 */
final class FormQuestionFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = FormQuestion::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'combination_id' => fake()->optional()->randomNumber(),
            'question_id' => fake()->randomNumber(),
            'form_id' => fake()->randomNumber(),
        ];
    }
}
