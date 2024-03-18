<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Student>
 */
final class StudentFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = Student::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'academic_id' => fake()->randomNumber(),
            'arabic_name' => fake()->word,
            'english_name' => fake()->word,
            'phone' => fake()->optional()->phoneNumber,
            'image_url' => fake()->optional()->word,
            'gender' => fake()->randomElement(['0', '1']),
            'birthdate' => fake()->optional()->dateTime(),
            'user_id' => fake()->optional()->uuid,
        ];
    }
}
