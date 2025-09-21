<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDayOff extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeDayOffFactory> */
    use HasFactory;
    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    protected $casts = [
        'date' => 'date',
        'weekday' => 'integer',
    ];

    public static function isDayOffOnDate(int $employeeId, Carbon $date): bool
    {


        return static::query()
            ->where('employee_id', $employeeId)
            ->where(function ($q) use ($date) {
                $q->whereDate('date', $date)
                ->orWhere('weekday', (int) $date->dayOfWeek);
            })
            ->exists();
    }


}
