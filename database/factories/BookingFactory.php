<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = $this->faker->dateTimeBetween('tomorrow', '+1 week');
        $duration = $this->faker->numberBetween(30, 120);
        $endAt = Carbon::parse($startAt)->addMinutes($duration);

        return [
            'employee_id' => Employee::factory(),
            'service_id' => Service::factory(),
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_email' => $this->faker->email(),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => BookingStatus::CONFIRMED,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::PENDING,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CONFIRMED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CANCELLED,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::COMPLETED,
        ]);
    }

    public function withTimeRange(Carbon $startAt, int $durationMinutes): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addMinutes($durationMinutes),
        ]);
    }

    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => $employee->id,
        ]);
    }

    public function forService(Service $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $service->id,
        ]);
    }
}
