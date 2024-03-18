<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Guest>
 */
final class GuestFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Guest::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'phone' => fake()->optional()->phoneNumber,
            'image_url' => fake()->optional()->word,
            'gender' => fake()->randomElement(['0', '1']),
            'user_id' => fake()->optional()->uuid,
        ];
    }
}
