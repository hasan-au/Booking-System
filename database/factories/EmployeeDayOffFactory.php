<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeDayOff>
 */
class EmployeeDayOffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'weekday' => $this->faker->numberBetween(0, 6), // Default to weekly recurring
            'date' => null,
        ];
    }

    public function weeklyRecurring(int $weekday = null): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => null,
            'weekday' => $weekday ?? $this->faker->numberBetween(0, 6), // 0 = Sunday, 6 = Saturday
        ]);
    }

    public function specificDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
            'weekday' => 0, // Still need to provide weekday due to NOT NULL constraint
        ]);
    }

    public function weekday(int $weekday): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => null,
            'weekday' => $weekday,
        ]);
    }
}
