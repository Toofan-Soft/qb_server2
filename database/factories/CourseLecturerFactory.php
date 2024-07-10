<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CourseLecturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CourseLecturer>
 */
final class CourseLecturerFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = CourseLecturer::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'academic_year' => fake()->randomNumber(),
            'department_course_part_id' => \App\Models\DepartmentCoursePart::factory(),
            'lecturer_id' => fake()->randomNumber(),
        ];
    }
}
