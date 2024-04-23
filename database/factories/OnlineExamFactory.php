<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OnlineExam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OnlineExam>
 */
final class OnlineExamFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = OnlineExam::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'id' => fake()->randomNumber(),
            'proctor_id' => fake()->randomNumber(),
            'status' => fake()->randomElement(['0', '1', '2']),
            'conduct_method' => fake()->randomElement(['0', '1']),
            'exam_datetime_notification_datetime' => fake()->dateTime(),
            'result_notification_datetime' => fake()->dateTime(),
        ];
    }
}
