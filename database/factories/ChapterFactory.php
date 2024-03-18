<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Chapter>
 */
final class ChapterFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Chapter::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'arabic_title' => fake()->word,
            'english_title' => fake()->word,
            'status' => fake()->randomElement(['0', '1']),
            'description' => fake()->optional()->text,
            'course_part_id' => fake()->randomNumber(),
        ];
    }
}
