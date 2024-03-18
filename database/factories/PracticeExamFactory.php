<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PracticeExam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PracticeExam>
 */
final class PracticeExamFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = PracticeExam::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'title' => fake()->optional()->title,
            'language' => fake()->randomElement(['0', '1']),
            'duration' => fake()->randomNumber(),
            'difficulty_level' => fake()->randomElement(['0', '1', '2', '3', '4']),
            'conduct_method' => fake()->randomElement(['0', '1']),
            'status' => fake()->randomElement(['0', '1', '2']),
            'department_course_part_id' => fake()->randomNumber(),
            'user_id' => fake()->uuid,
            'department_course_parts_id' => \App\Models\DepartmentCoursePart::factory(),
        ];
    }
}
