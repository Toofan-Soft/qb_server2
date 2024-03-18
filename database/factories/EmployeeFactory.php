<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Employee>
 */
final class EmployeeFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Employee::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'arabic_name' => fake()->word,
            'english_name' => fake()->word,
            'phone' => fake()->optional()->phoneNumber,
            'image_url' => fake()->optional()->word,
            'job_type' => fake()->randomElement(['0', '1', '2']),
            'qualification ' => fake()->randomElement(['0', '1', '2', '3', '4', '5']),
            'specialization ' => fake()->optional()->word,
            'gender' => fake()->randomElement(['0', '1']),
            'user_id' => fake()->optional()->uuid,
        ];
    }
}
