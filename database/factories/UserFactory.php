<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = User::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'id' => fake()->uuid,
            'email' => fake()->safeEmail,
            'email_verified_at' => fake()->optional()->dateTime(),
            'password' => bcrypt(fake()->password),
            'status' => fake()->randomElement(['0', '1']),
            'owner_type' => fake()->randomElement(['0', '1', '2', '3']),
            'remember_token' => Str::random(10),
        ];
    }
}
