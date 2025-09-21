<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'work_start_time',
        'work_end_time',
        'photo',
        'job_title',
        'bio',
        'rating',
    ];

    protected $casts = [
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'employee_service', 'employee_id', 'service_id');
    }


    public function employeeDayOffs(): HasMany
    {
        return $this->hasMany(EmployeeDayOff::class, 'employee_id', 'id');
    }


    public function employeeBreaks(): HasMany
    {
        return $this->hasMany(EmployeeBreak::class, 'employee_id', 'id');
    }


    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'employee_id', 'id');
    }

    public function getAllDaysOffAsDate()
    {
        $specificDates = $this->employeeDayOffs()->pluck('date')->toArray();
        $weekdays = $this->employeeDayOffs()->where('date', null)->pluck('weekday')->toArray();
        //convert weekdays to dates for the next 30 days
        $datesFromWeekdays = [];
        $startDate = now();
        $endDate = now()->addDays(30);
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (in_array($date->dayOfWeek, $weekdays)) {
                $datesFromWeekdays[] = $date->toDateString();
            }
        }
        return array_unique(array_merge($specificDates, $datesFromWeekdays));

    }


}
