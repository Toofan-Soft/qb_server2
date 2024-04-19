<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RealExam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\RealExam>
 */
final class RealExamFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = RealExam::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'language' => fake()->randomElement(['0', '1']),
            'difficulty_level' => fake()->randomElement(['0', '1', '2', '3', '4']),
            'form_configuration_method' => fake()->randomElement(['0', '1', '2']),
            'forms_count' => fake()->randomNumber(),
            'form_name_method' => fake()->randomElement(['0', '1', '2']),
            'datetime' => fake()->dateTime(),
            'duration' => fake()->randomNumber(),
            'type' => fake()->randomElement(['0', '1']),
            'exam_type' => fake()->randomElement(['0', '1', '2']),
            'note' => fake()->optional()->sentence,
            'course_lecturer_id' => fake()->randomNumber(),
        ];
    }
}
