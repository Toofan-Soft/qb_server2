<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Department>
 */
final class DepartmentFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Department::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'arabic_name' => fake()->unique()->word,
            'english_name' => fake()->unique()->word,
            'logo_url' => fake()->optional()->word,
            'levels_count' => fake()->randomElement(['2', '3', '4', '5', '6', '7']),
            'description' => fake()->optional()->text,
            'college_id' => fake()->randomNumber(),
        ];
    }
}
