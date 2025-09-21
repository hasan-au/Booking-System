<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeBreak>
 */
class EmployeeBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->time('H:i:s');
        $endTime = \Carbon\Carbon::parse($startTime)->addMinutes($this->faker->numberBetween(15, 120))->format('H:i:s');

        return [
            'employee_id' => Employee::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_recurring' => false,
            'date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'weekday' => null,
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'date' => null,
            'weekday' => $this->faker->numberBetween(0, 6), // 0 = Sunday, 6 = Saturday
        ]);
    }

    public function oneTime(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => false,
            'date' => $date,
            'weekday' => null,
        ]);
    }

    public function lunchBreak(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'is_recurring' => true,
            'date' => null,
        ]);
    }
}
