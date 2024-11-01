<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\UserRole>
 */
final class UserRoleFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = UserRole::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'role_id' => fake()->randomElement(['0', '1', '2', '3', '4', '5', '6', '7']),
            'user_id' => fake()->uuid,
        ];
    }
}
