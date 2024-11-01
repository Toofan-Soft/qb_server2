<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Topic>
 */
final class TopicFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Topic::class;

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
            'description' => fake()->optional()->text,
            'chapter_id' => fake()->randomNumber(),
        ];
    }
}
