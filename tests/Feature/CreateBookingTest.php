<?php

use App\Models\{Employee, Service, Booking, EmployeeDayOff, EmployeeBreak};
use App\Services\{BookingService, AvailabilityService};
use App\Enums\{BookingStatus, ServiceStatus};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2025-09-22 08:00:00');
    Config::set('app.timezone', 'Australia/Melbourne');
});

describe('Core Booking Functionality', function () {
    it('can create a successful booking with valid data', function () {
        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $service = Service::factory()->create([
            'duration_minutes' => 60,
            'status' => ServiceStatus::ACTIVE,
        ]);

        // Attach service to employee
        $employee->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer = [
            'name' => 'John Doe',
            'phone' => '+61400123456',
            'email' => 'john@example.com',
        ];

        $bookingService = new BookingService();
        $booking = $bookingService->create($employee, $service, $startAt, $customer);

        expect($booking)->toBeInstanceOf(Booking::class)
            ->and($booking->employee_id)->toBe($employee->id)
            ->and($booking->service_id)->toBe($service->id)
            ->and($booking->customer_name)->toBe('John Doe')
            ->and($booking->customer_phone)->toBe('+61400123456')
            ->and($booking->customer_email)->toBe('john@example.com')
            ->and($booking->start_at->toDateTimeString())->toBe($startAt->toDateTimeString())
            ->and($booking->end_at->toDateTimeString())->toBe($startAt->copy()->addHour()->toDateTimeString())
            ->and($booking->status)->toBe(BookingStatus::CONFIRMED);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'customer_name' => 'John Doe',
        ]);
    });

    it('creates booking with correct end time based on service duration', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 90]);
        $employee->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 14:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Jane Doe'];

        $bookingService = new BookingService();
        $booking = $bookingService->create($employee, $service, $startAt, $customer);

        expect($booking->end_at->toDateTimeString())->toBe('2025-09-23 15:30:00');
    });

    it('can create booking with different status', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();
        $employee->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 11:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();
        $booking = $bookingService->create($employee, $service, $startAt, $customer, BookingStatus::PENDING);

        expect($booking->status)->toBe(BookingStatus::PENDING);
    });

    it('generates uuid for each booking', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();
        $employee->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 12:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();
        $booking = $bookingService->create($employee, $service, $startAt, $customer);

        expect($booking->uuid)->not->toBeNull()
            ->and($booking->uuid)->not->toBeEmpty()
            ->and((string) $booking->uuid)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
    });
});

describe('Booking Overlap Prevention', function () {
    it('prevents overlapping bookings for the same employee', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        // Create first booking
        $firstStartAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'First Customer'];

        $bookingService = new BookingService();
        $firstBooking = $bookingService->create($employee, $service, $firstStartAt, $customer);

        // Try to create overlapping booking (should fail)
        $overlappingStartAt = Carbon::parse('2025-09-23 10:30:00', 'Australia/Melbourne');
        $overlappingCustomer = ['name' => 'Overlapping Customer'];

        expect(fn() => $bookingService->create($employee, $service, $overlappingStartAt, $overlappingCustomer))
            ->toThrow(RuntimeException::class, 'The Employee has another booking that overlaps with the selected time.');
    });

    it('allows bookings that start exactly when another ends', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        // Create first booking (10:00-11:00)
        $firstStartAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer1 = ['name' => 'First Customer'];

        $bookingService = new BookingService();
        $firstBooking = $bookingService->create($employee, $service, $firstStartAt, $customer1);

        // Create second booking starting exactly when first ends (11:00-12:00)
        $secondStartAt = Carbon::parse('2025-09-23 11:00:00', 'Australia/Melbourne');
        $customer2 = ['name' => 'Second Customer'];

        $secondBooking = $bookingService->create($employee, $service, $secondStartAt, $customer2);

        expect($secondBooking)->toBeInstanceOf(Booking::class)
            ->and($secondBooking->start_at->toDateTimeString())->toBe('2025-09-23 11:00:00');
    });

    it('prevents booking that starts before but ends during existing booking', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        // Create existing booking (10:00-11:00)
        $existingStartAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer1 = ['name' => 'Existing Customer'];

        $bookingService = new BookingService();
        $existingBooking = $bookingService->create($employee, $service, $existingStartAt, $customer1);

        // Try to create booking that starts before but overlaps (09:30-10:30)
        $overlappingStartAt = Carbon::parse('2025-09-23 09:30:00', 'Australia/Melbourne');
        $customer2 = ['name' => 'Overlapping Customer'];

        expect(fn() => $bookingService->create($employee, $service, $overlappingStartAt, $customer2))
            ->toThrow(RuntimeException::class, 'The Employee has another booking that overlaps with the selected time.');
    });

    it('allows bookings for different employees at the same time', function () {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $employee1->services()->attach($service->id);
        $employee2->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer1 = ['name' => 'Customer 1'];
        $customer2 = ['name' => 'Customer 2'];

        $bookingService = new BookingService();
        $booking1 = $bookingService->create($employee1, $service, $startAt, $customer1);
        $booking2 = $bookingService->create($employee2, $service, $startAt, $customer2);

        expect($booking1)->toBeInstanceOf(Booking::class)
            ->and($booking2)->toBeInstanceOf(Booking::class)
            ->and($booking1->employee_id)->not->toBe($booking2->employee_id);
    });

    it('only considers blocking statuses for overlap check', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        // Create a cancelled booking
        Booking::factory()->create([
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'start_at' => Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne'),
            'end_at' => Carbon::parse('2025-09-23 11:00:00', 'Australia/Melbourne'),
            'status' => BookingStatus::CANCELLED,
        ]);

        // Try to create new booking at the same time (should succeed)
        $startAt = Carbon::parse('2025-09-23 10:30:00', 'Australia/Melbourne');
        $customer = ['name' => 'New Customer'];

        $bookingService = new BookingService();
        $newBooking = $bookingService->create($employee, $service, $startAt, $customer);

        expect($newBooking)->toBeInstanceOf(Booking::class);
    });
});

describe('Employee Day Off Validation', function () {
    it('prevents booking on employee specific day off', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();
        $employee->services()->attach($service->id);

        // Create specific day off
        EmployeeDayOff::factory()->specificDate('2025-09-23')->create([
            'employee_id' => $employee->id,
        ]);

        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();

        expect(fn() => $bookingService->create($employee, $service, $startAt, $customer))
            ->toThrow(RuntimeException::class, 'The Employee has a day off on the selected date.');
    });

    it('prevents booking on employee weekly day off', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();
        $employee->services()->attach($service->id);

        // Create weekly day off (Monday = 1)
        EmployeeDayOff::factory()->weekday(1)->create([
            'employee_id' => $employee->id,
        ]);

        // September 22, 2025 is a Monday
        $startAt = Carbon::parse('2025-09-22 10:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();

        expect(fn() => $bookingService->create($employee, $service, $startAt, $customer))
            ->toThrow(RuntimeException::class, 'The Employee has a day off on the selected date.');
    });

    it('allows booking on available days', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();
        $employee->services()->attach($service->id);

        // Create day off for different date
        EmployeeDayOff::factory()->specificDate('2025-09-24')->create([
            'employee_id' => $employee->id,
        ]);

        // Try to book on available date
        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();
        $booking = $bookingService->create($employee, $service, $startAt, $customer);

        expect($booking)->toBeInstanceOf(Booking::class);
    });
});

describe('Booking Update Functionality', function () {
    it('can update booking details successfully', function () {
        $originalEmployee = Employee::factory()->create();
        $newEmployee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $originalEmployee->services()->attach($service->id);
        $newEmployee->services()->attach($service->id);

        // Create original booking
        $originalBooking = Booking::factory()->create([
            'employee_id' => $originalEmployee->id,
            'service_id' => $service->id,
            'start_at' => Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne'),
            'end_at' => Carbon::parse('2025-09-23 11:00:00', 'Australia/Melbourne'),
            'customer_name' => 'Original Name',
        ]);

        $newStartAt = Carbon::parse('2025-09-23 14:00:00', 'Australia/Melbourne');
        $newCustomer = [
            'name' => 'Updated Name',
            'phone' => '+61400999888',
            'email' => 'updated@example.com',
        ];

        $bookingService = new BookingService();
        $updatedBooking = $bookingService->update(
            $originalBooking,
            $newEmployee,
            $service,
            $newStartAt,
            $newCustomer,
            BookingStatus::PENDING
        );

        expect($updatedBooking->employee_id)->toBe($newEmployee->id)
            ->and($updatedBooking->start_at->toDateTimeString())->toBe('2025-09-23 14:00:00')
            ->and($updatedBooking->end_at->toDateTimeString())->toBe('2025-09-23 15:00:00')
            ->and($updatedBooking->customer_name)->toBe('Updated Name')
            ->and($updatedBooking->status)->toBe(BookingStatus::PENDING);
    });

    it('prevents update to overlapping time slot', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        // Create existing booking
        $existingBooking = Booking::factory()->create([
            'employee_id' => $employee->id,
            'start_at' => Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne'),
            'end_at' => Carbon::parse('2025-09-23 11:00:00', 'Australia/Melbourne'),
        ]);

        // Create booking to update
        $bookingToUpdate = Booking::factory()->create([
            'employee_id' => $employee->id,
            'start_at' => Carbon::parse('2025-09-23 14:00:00', 'Australia/Melbourne'),
            'end_at' => Carbon::parse('2025-09-23 15:00:00', 'Australia/Melbourne'),
        ]);

        // Try to update to overlapping time
        $overlappingStartAt = Carbon::parse('2025-09-23 10:30:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();

        expect(fn() => $bookingService->update($bookingToUpdate, $employee, $service, $overlappingStartAt, $customer))
            ->toThrow(RuntimeException::class, 'The Employee has another booking that overlaps with the selected time.');
    });

    it('allows update to same time slot (no change)', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        $originalStartAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $booking = Booking::factory()->create([
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'start_at' => $originalStartAt,
            'end_at' => $originalStartAt->copy()->addHour(),
            'customer_name' => 'Original Name',
        ]);

        $updatedCustomer = ['name' => 'Updated Name'];

        $bookingService = new BookingService();
        $updatedBooking = $bookingService->update($booking, $employee, $service, $originalStartAt, $updatedCustomer);

        expect($updatedBooking->customer_name)->toBe('Updated Name')
            ->and($updatedBooking->start_at->toDateTimeString())->toBe($originalStartAt->toDateTimeString());
    });
});

describe('Availability Service Tests', function () {
    it('returns empty slots for past dates', function () {
        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $pastDate = Carbon::parse('2025-09-21', 'Australia/Melbourne'); // Yesterday

        $availabilityService = new AvailabilityService();
        $slots = $availabilityService->getSlotsForDate($employee, $service, $pastDate);

        expect($slots)->toBeEmpty();
    });

    it('returns empty slots for employee day off', function () {
        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
        $service = Service::factory()->create(['duration_minutes' => 60]);

        // Create day off for the specific date
        EmployeeDayOff::factory()->specificDate('2025-09-23')->create([
            'employee_id' => $employee->id,
        ]);

        $dayOffDate = Carbon::parse('2025-09-23', 'Australia/Melbourne');

        $availabilityService = new AvailabilityService();
        $slots = $availabilityService->getSlotsForDate($employee, $service, $dayOffDate);

        expect($slots)->toBeEmpty();
    });

    it('excludes booked time slots from available slots', function () {
        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
        $service = Service::factory()->create(['duration_minutes' => 60]);

        // Create existing booking
        Booking::factory()->create([
            'employee_id' => $employee->id,
            'start_at' => Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne'),
            'end_at' => Carbon::parse('2025-09-23 11:00:00', 'Australia/Melbourne'),
            'status' => BookingStatus::CONFIRMED,
        ]);

        $date = Carbon::parse('2025-09-23', 'Australia/Melbourne');

        $availabilityService = new AvailabilityService();
        $slots = $availabilityService->getSlotsForDate($employee, $service, $date);

        // Should not include 10:00, 10:15, 10:30, 10:45 (as these would overlap with existing booking)
        expect($slots)->not->toContain('2025-09-23 10:00:00')
            ->and($slots)->not->toContain('2025-09-23 10:15:00')
            ->and($slots)->not->toContain('2025-09-23 10:30:00')
            ->and($slots)->not->toContain('2025-09-23 10:45:00');
    });

    it('excludes employee break times from available slots', function () {
        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
        $service = Service::factory()->create(['duration_minutes' => 60]);

        // Create employee break for Tuesday (September 23, 2025 is Tuesday = day 2)
        EmployeeBreak::factory()->lunchBreak()->create([
            'employee_id' => $employee->id,
            'is_recurring' => true,
            'weekday' => 2, // Tuesday
            'date' => null,
        ]);

        $date = Carbon::parse('2025-09-23', 'Australia/Melbourne'); // This is a Tuesday

        $availabilityService = new AvailabilityService();
        $slots = $availabilityService->getSlotsForDate($employee, $service, $date);

        // Should not include lunch break slots
        expect($slots)->not->toContain('2025-09-23 12:00:00')
            ->and($slots)->not->toContain('2025-09-23 12:15:00')
            ->and($slots)->not->toContain('2025-09-23 12:30:00')
            ->and($slots)->not->toContain('2025-09-23 12:45:00');
    });

    it('adjusts start time for today with lead time', function () {
        // Set current time to 10:30 AM
        Carbon::setTestNow('2025-09-22 10:30:00');

        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
        $service = Service::factory()->create(['duration_minutes' => 60]);

        $today = Carbon::parse('2025-09-22', 'Australia/Melbourne');

        $availabilityService = new AvailabilityService();
        $slots = $availabilityService->getSlotsForDate($employee, $service, $today, 15, 0, 0, 5);

        // With 5 minutes lead time, earliest slot should be 10:45 (rounded up to 15-minute step)
        expect($slots)->toContain('2025-09-22 10:45:00')
            ->and($slots)->not->toContain('2025-09-22 10:30:00')
            ->and($slots)->not->toContain('2025-09-22 10:00:00');
    });
});

describe('Edge Cases and Error Handling', function () {
    it('handles minimum customer data requirements', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 30]);
        $employee->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $minimalCustomer = ['name' => 'John']; // Only name provided

        $bookingService = new BookingService();
        $booking = $bookingService->create($employee, $service, $startAt, $minimalCustomer);

        expect($booking->customer_name)->toBe('John')
            ->and($booking->customer_phone)->toBeNull()
            ->and($booking->customer_email)->toBeNull();
    });

    it('handles bookings at work day boundaries', function () {
        $employee = Employee::factory()->create([
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
        ]);
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        // Booking at exact start time
        $startAtWorkStart = Carbon::parse('2025-09-23 09:00:00', 'Australia/Melbourne');
        $customer1 = ['name' => 'Early Customer'];

        // Booking ending at exact end time
        $startAtWorkEnd = Carbon::parse('2025-09-23 16:00:00', 'Australia/Melbourne');
        $customer2 = ['name' => 'Late Customer'];

        $bookingService = new BookingService();

        $earlyBooking = $bookingService->create($employee, $service, $startAtWorkStart, $customer1);
        $lateBooking = $bookingService->create($employee, $service, $startAtWorkEnd, $customer2);

        expect($earlyBooking->start_at->toTimeString())->toBe('09:00:00')
            ->and($lateBooking->end_at->toTimeString())->toBe('17:00:00');
    });

    it('handles concurrent booking attempts with database locking', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $employee->services()->attach($service->id);

        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer1 = ['name' => 'Customer 1'];
        $customer2 = ['name' => 'Customer 2'];

        $bookingService = new BookingService();

        // First booking should succeed
        $booking1 = $bookingService->create($employee, $service, $startAt, $customer1);
        expect($booking1)->toBeInstanceOf(Booking::class);

        // Second booking at same time should fail
        expect(fn() => $bookingService->create($employee, $service, $startAt, $customer2))
            ->toThrow(RuntimeException::class);
    });

    it('validates service duration affects booking end time correctly', function () {
        $employee = Employee::factory()->create();
        $shortService = Service::factory()->create(['duration_minutes' => 15]);
        $longService = Service::factory()->create(['duration_minutes' => 180]);

        $employee->services()->attach([$shortService->id, $longService->id]);

        $startAt = Carbon::parse('2025-09-23 10:00:00', 'Australia/Melbourne');
        $customer = ['name' => 'Test Customer'];

        $bookingService = new BookingService();

        $shortBooking = $bookingService->create($employee, $shortService, $startAt, $customer);
        $longBooking = $bookingService->create($employee, $longService, $startAt->copy()->addHours(4), $customer);

        expect($shortBooking->end_at->toTimeString())->toBe('10:15:00')
            ->and($longBooking->end_at->toTimeString())->toBe('17:00:00'); // 14:00 + 3 hours
    });
});

describe('Booking Model Scopes and Relationships', function () {
    it('tests overlap scope correctly identifies overlapping bookings', function () {
        $employee = Employee::factory()->create();

        $existingBooking = Booking::factory()->create([
            'employee_id' => $employee->id,
            'start_at' => Carbon::parse('2025-09-23 10:00:00'),
            'end_at' => Carbon::parse('2025-09-23 11:00:00'),
            'status' => BookingStatus::CONFIRMED,
        ]);

        // Test exact overlap
        $exactOverlap = Booking::overlap($employee->id,
            Carbon::parse('2025-09-23 10:00:00'),
            Carbon::parse('2025-09-23 11:00:00')
        )->exists();
        expect($exactOverlap)->toBeTrue();

        // Test partial overlap (starts during existing booking)
        $partialOverlap = Booking::overlap($employee->id,
            Carbon::parse('2025-09-23 10:30:00'),
            Carbon::parse('2025-09-23 11:30:00')
        )->exists();
        expect($partialOverlap)->toBeTrue();

        // Test no overlap
        $noOverlap = Booking::overlap($employee->id,
            Carbon::parse('2025-09-23 11:00:00'),
            Carbon::parse('2025-09-23 12:00:00')
        )->exists();
        expect($noOverlap)->toBeFalse();
    });

    it('tests blocking statuses correctly', function () {
        $blockingStatuses = Booking::blockingStatuses();

        expect($blockingStatuses)->toContain('confirmed')
            ->and($blockingStatuses)->toContain('in_progress')
            ->and($blockingStatuses)->toContain('pending')
            ->and($blockingStatuses)->not->toContain('cancelled')
            ->and($blockingStatuses)->not->toContain('completed');
    });

    it('tests booking relationships', function () {
        $employee = Employee::factory()->create();
        $service = Service::factory()->create();

        $booking = Booking::factory()->create([
            'employee_id' => $employee->id,
            'service_id' => $service->id,
        ]);

        expect($booking->employee)->toBeInstanceOf(Employee::class)
            ->and($booking->employee->id)->toBe($employee->id)
            ->and($booking->service)->toBeInstanceOf(Service::class)
            ->and($booking->service->id)->toBe($service->id);
    });
});

?>
