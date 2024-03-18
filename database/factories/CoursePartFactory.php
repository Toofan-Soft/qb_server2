<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CoursePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CoursePart>
 */
final class CoursePartFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = CoursePart::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'course_id' => fake()->randomNumber(),
            'part_id' => fake()->randomElement(['0', '1', '2']),
            'status' => fake()->randomElement(['0', '1']),
            'description' => fake()->optional()->text,
        ];
    }
}
