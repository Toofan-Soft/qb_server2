<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StudentOnlineExam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StudentOnlineExam>
 */
final class StudentOnlineExamFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = StudentOnlineExam::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'start_datetime' => fake()->optional()->dateTime(),
            'end_datetime' => fake()->optional()->dateTime(),
            'status' => fake()->randomElement(['0', '1', '2', '3']),
            'student_id' => fake()->randomNumber(),
            'online_exam_id' => fake()->randomNumber(),
        ];
    }
}
