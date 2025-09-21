<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\EmployeeBreak;
use App\Models\EmployeeDayOff;
use App\Models\Service as SalonService;
use Carbon\Carbon;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

class AvailabilityService
{
    const CURRENT_TIMEZONE = 'Australia/Melbourne';

    public function getSlotsForDate(
        Employee $employee,
        SalonService $service,
        Carbon $date,
        int $slotStepMinutes = 15,
        int $bufferBefore = 0,
        int $bufferAfter = 0,
        int $leadMinutesIfToday = 5
        ): array
    {
        // if the date is in the past day, return empty array
        if($date->copy()->setTimezone(self::CURRENT_TIMEZONE)->lt(Carbon::today(self::CURRENT_TIMEZONE))) {
            return [];
        }
        // check if the employee works on that day
        if(EmployeeDayOff::isDayOffOnDate($employee->id, $date)) {
            return [];
        }



        $workDayStart = $date->copy()->setTimezone(self::CURRENT_TIMEZONE)
            ->setTime($employee->work_start_time->hour, $employee->work_start_time->minute, 0);
        $workDayEnd = $date->copy()->setTimezone(self::CURRENT_TIMEZONE)
            ->setTime($employee->work_end_time->hour, $employee->work_end_time->minute, 0);

        // is the date is today, adjust the workDayStart to be at least leadMinutesIfToday from now
        if ($date->copy()->setTimezone(self::CURRENT_TIMEZONE)->isSameDay(now(self::CURRENT_TIMEZONE))) {
            $now = now(self::CURRENT_TIMEZONE);
            $roundedNow = $this->roundUpToStep($now->copy()->addMinutes($leadMinutesIfToday), $slotStepMinutes);
            if ($roundedNow->gt($workDayStart)) {
                $workDayStart = $roundedNow;
            }
        }
        // if the work day start is after or equal to work day end, return empty array
        if ($workDayStart->gte($workDayEnd)) {
            return [];
        }
        // dd($workDayStart);

        $workPeriod = Period::make(
            $workDayStart,
            $workDayEnd,
            Precision::MINUTE(),
            boundaries: Boundaries::EXCLUDE_END()
        );

        $occupied = new PeriodCollection();

        $bookings = Booking::query()
        ->where('employee_id', $employee->id)
        ->whereIn('status', Booking::blockingStatuses())
        ->where('start_at', '>=', $workDayStart)
        ->where('end_at', '<=', $workDayEnd)
        ->get();
        // ->chunk(100, function ($chunk) use ($occupied) {
        //     foreach ($chunk as $booking) {
        //         $occupied->add(Period::make(
        //             $booking->start_at,
        //             $booking->end_at,
        //             Precision::MINUTE(),
        //             boundaries: Boundaries::EXCLUDE_END()
        //         ));
        //     }
        // });
        foreach ($bookings as $booking) {
            $occupied = $this->addPeriodToCollection($booking, $occupied, $bufferBefore, $bufferAfter);
        }

        // add employee breaks to occupied
        $breaks = $this->getEmployeeBreaksForDate($employee, $date, $workDayStart, $workDayEnd);

        foreach ($breaks as $break) {
            $occupied = $this->addPeriodToCollection($break, $occupied, $bufferBefore, $bufferAfter);
        }


        $available = $workPeriod->subtract(...$occupied);
        $duration = (int) $service->duration_minutes;
        $slots = [];

        foreach ($available as $period) {
            $slotStart = Carbon::parse($period->start(), self::CURRENT_TIMEZONE);
            $slotEnd = Carbon::parse($period->end(), self::CURRENT_TIMEZONE)->subMinutes($duration);

            while ($slotStart->lte($slotEnd)) {
                $slots[] = $slotStart->toDateTimeString();
                $slotStart->addMinutes($slotStepMinutes);
            }
        }

        return $slots;

    }

    public function getSlotsForUpdateBooking(
        Booking $booking,
        Employee $employee,
        SalonService $service,
        Carbon $date,
        int $slotStepMinutes = 15,
        int $bufferBefore = 0,
        int $bufferAfter = 0,
        int $leadMinutesIfToday = 5
    ): array
    {


        if($date->copy()->setTimezone(self::CURRENT_TIMEZONE)->lt(Carbon::today(self::CURRENT_TIMEZONE))) {
            return [];
        }

        if(EmployeeDayOff::isDayOffOnDate($employee->id, $date)) {
            return [];
        }



        $workDayStart = $date->copy()->setTimezone(self::CURRENT_TIMEZONE)
            ->setTime($employee->work_start_time->hour, $employee->work_start_time->minute, 0);
        $workDayEnd = $date->copy()->setTimezone(self::CURRENT_TIMEZONE)
            ->setTime($employee->work_end_time->hour, $employee->work_end_time->minute, 0);

        if ($date->copy()->setTimezone(self::CURRENT_TIMEZONE)->isSameDay(now(self::CURRENT_TIMEZONE))) {
            $now = now(self::CURRENT_TIMEZONE);
            $roundedNow = $this->roundUpToStep($now->copy()->addMinutes($leadMinutesIfToday), $slotStepMinutes);
            if ($roundedNow->gt($workDayStart)) {
                $workDayStart = $roundedNow;
            }
        }

        if ($workDayStart->gte($workDayEnd)) {
            return [];
        }


        $workPeriod = Period::make(
            $workDayStart,
            $workDayEnd,
            Precision::MINUTE(),
            boundaries: Boundaries::EXCLUDE_END()
        );

        $occupied = new PeriodCollection();

        $bookings = Booking::query()
        ->where('id', '!=', $booking->id)
        ->where('employee_id', $employee->id)
        ->whereIn('status', Booking::blockingStatuses())
        ->where('start_at', '>=', $workDayStart)
        ->where('end_at', '<=', $workDayEnd)
        ->get();

        foreach ($bookings as $booking) {
            $occupied = $this->addPeriodToCollection($booking, $occupied, $bufferBefore, $bufferAfter);
        }


        $breaks = $this->getEmployeeBreaksForDate($employee, $date, $workDayStart, $workDayEnd);

        foreach ($breaks as $break) {
            $occupied = $this->addPeriodToCollection($break, $occupied, $bufferBefore, $bufferAfter);
        }


        $available = $workPeriod->subtract(...$occupied);
        $duration = (int) $service->duration_minutes;
        $slots = [];

        foreach ($available as $period) {
            $slotStart = Carbon::parse($period->start(), self::CURRENT_TIMEZONE);
            $slotEnd = Carbon::parse($period->end(), self::CURRENT_TIMEZONE)->subMinutes($duration);

            while ($slotStart->lte($slotEnd)) {
                $slots[] = $slotStart->toDateTimeString();
                $slotStart->addMinutes($slotStepMinutes);
            }
        }

        return $slots;

    }

    protected function getEmployeeBreaksForDate(Employee $employee, Carbon $date, Carbon $workDayStart, Carbon $workDayEnd)
    {
        $breaks = EmployeeBreak::forEmployeeOnDate($employee->id, $date)->get();

        return $breaks->map(function ($break) use ($date, $workDayStart, $workDayEnd) {
            // Create start and end times for the break on the specific date with timezone
            $breakStart = $date->copy()->setTimezone(self::CURRENT_TIMEZONE)
                ->setTimeFromTimeString($break->start_time);
            $breakEnd = $date->copy()->setTimezone(self::CURRENT_TIMEZONE)
                ->setTimeFromTimeString($break->end_time);

            // Only include breaks that fall within the work day
            if ($breakStart->gte($workDayStart) && $breakEnd->lte($workDayEnd)) {
                return (object) [
                    'start_at' => $breakStart->toDateTimeString(),
                    'end_at' => $breakEnd->toDateTimeString(),
                ];
            }

            return null;
        })->filter(); // Remove null values
    }

    protected function addPeriodToCollection($reservedTime, PeriodCollection $occupied, int $bufferBefore = 0, int $bufferAfter = 0)
    {
        $start = Carbon::parse($reservedTime->start_at, self::CURRENT_TIMEZONE)->subMinutes($bufferBefore);
        $end = Carbon::parse($reservedTime->end_at, self::CURRENT_TIMEZONE)->addMinutes($bufferAfter);

        return $occupied->add(Period::make(
            $start,
            $end,
            Precision::MINUTE(),
            boundaries: Boundaries::EXCLUDE_END()
        ));
    }

    protected function roundUpToStep(Carbon $time, int $step): Carbon
    {
        $mins = (int) $time->copy()->format('i');
        $add  = ($step - ($mins % $step)) % $step;
        return $time->copy()->addMinutes($add)->second(0);
    }

}
