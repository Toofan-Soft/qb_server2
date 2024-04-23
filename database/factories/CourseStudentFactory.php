<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CourseStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CourseStudent>
 */
final class CourseStudentFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = CourseStudent::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'department_course_id' => fake()->randomNumber(),
            'student_id' => fake()->randomNumber(),
            'status' => fake()->randomElement(['0', '1', '2']),
            'academic_year' => fake()->randomNumber(),
        ];
    }
}
