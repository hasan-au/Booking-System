<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
            'photo'=> $this->faker->imageUrl(640, 480, 'people', true),
            'job_title' => $this->faker->jobTitle(),
            'bio' => $this->faker->paragraph(),
            'rating' => $this->faker->randomFloat(1, 1, 5),
        ];
    }
}
