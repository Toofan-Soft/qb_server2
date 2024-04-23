<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DepartmentCoursePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DepartmentCoursePart>
 */
final class DepartmentCoursePartFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = DepartmentCoursePart::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'department_course_id' => fake()->randomNumber(),
            'course_part_id' => fake()->randomNumber(),
            'note' => fake()->optional()->sentence,
            'score' => fake()->optional()->randomNumber(),
            'lectures_count' => fake()->optional()->randomNumber(),
            'lecture_duration' => fake()->optional()->randomNumber(),
        ];
    }
}
