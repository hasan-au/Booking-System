<?php

// app/Services/BookingService.php
namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Service as SalonService;
use App\Models\EmployeeOffDay;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use RuntimeException;
use App\Models\EmployeeDayOff;

class BookingService
{

    public function create(
        Employee $employee,
        SalonService $service,
        Carbon $startAt,
        array $customer,
        BookingStatus|string $status = BookingStatus::CONFIRMED,
    ): Booking {
        $endAt = $startAt->copy()->addMinutes((int)$service->duration_minutes);

        return DB::transaction(function () use ($employee, $service, $startAt, $endAt, $customer, $status) {

            if (EmployeeDayOff::isDayOffOnDate($employee->id, $startAt)) {
                throw new RuntimeException('The Employee has a day off on the selected date.');
            }

            // disable for edit
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();


            $overlap = Booking::query()
                ->overlap($employee->id, $startAt, $endAt)
                ->exists();

            if ($overlap) {
                throw new RuntimeException('The Employee has another booking that overlaps with the selected time.');
            }


            return Booking::create([
                'employee_id'   => $employee->id,
                'service_id'    => $service->id,
                'customer_name' => $customer['name'],
                'customer_phone'=> $customer['phone'] ?? null,
                'customer_email'=> $customer['email'] ?? null,
                'start_at'      => $startAt,
                'end_at'        => $endAt,
                'status'        => $status,
            ]);
        });
    }


    public function update(
        Booking $booking,
        Employee $employee,
        SalonService $service,
        Carbon $startAt,
        array $customer,
        BookingStatus|string|null $status = null, // اتركه null ليبقى كما هو إن لم ترسله
    ): Booking {
        $endAt = $startAt->copy()->addMinutes((int) $service->duration_minutes);

        return DB::transaction(function () use ($booking, $employee, $service, $startAt, $endAt, $customer, $status) {


            if (EmployeeDayOff::isDayOffOnDate($employee->id, $startAt)) {
                throw new RuntimeException('The Employee has a day off on the selected date.');
            }


            $employeeIdsToLock = array_unique([$employee->id, $booking->employee_id]);
            DB::table('employees')->whereIn('id', $employeeIdsToLock)->lockForUpdate()->get();


            DB::table('bookings')->where('id', $booking->id)->lockForUpdate()->first();


            $overlap = Booking::query()
                ->where('id', '!=', $booking->id) // exclude current booking from overlap check
                ->overlap($employee->id, $startAt, $endAt)
                ->exists();

            if ($overlap) {
                throw new RuntimeException('The Employee has another booking that overlaps with the selected time.');
            }


            $booking->fill([
                'employee_id'    => $employee->id,
                'service_id'     => $service->id,
                'customer_name'  => $customer['name'],
                'customer_phone' => $customer['phone'] ?? null,
                'customer_email' => $customer['email'] ?? null,
                'start_at'       => $startAt,
                'end_at'         => $endAt,
            ]);


            if (! is_null($status)) {
                $booking->status = $status;
            }

            $booking->save();

            return $booking;
        });
    }
}


?>
