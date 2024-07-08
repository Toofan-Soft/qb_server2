<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DepartmentCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DepartmentCourse>
 */
final class DepartmentCourseFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = DepartmentCourse::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'level' => fake()->randomElement(['1', '2', '3', '4', '5', '6', '7']),
            'semester' => fake()->randomElement(['1', '2']),
            'course_id' => fake()->randomNumber(),
            'department_id' => fake()->randomNumber(),
        ];
    }
}
