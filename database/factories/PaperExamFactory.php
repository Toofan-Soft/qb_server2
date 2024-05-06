<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaperExam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PaperExam>
 */
final class PaperExamFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = PaperExam::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'id' => fake()->randomNumber(),
            'course_lecturer_name' => fake()->word,
        ];
    }
}
