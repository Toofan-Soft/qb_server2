<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DepartmentCoursePartTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DepartmentCoursePartTopic>
 */
final class DepartmentCoursePartTopicFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = DepartmentCoursePartTopic::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'department_course_part_id' => fake()->randomNumber(),
            'topic_id' => fake()->randomNumber(),
        ];
    }
}
