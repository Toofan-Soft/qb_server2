<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\College>
 */
final class CollegeFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = College::class;

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
            'description' => fake()->optional()->text,
            'phone' => fake()->optional()->phoneNumber,
            'email' => fake()->optional()->safeEmail,
            'facebook' => fake()->optional()->word,
            'x_platform' => fake()->optional()->word,
            'youtube' => fake()->optional()->word,
            'telegram' => fake()->optional()->word,
        ];
    }
}
