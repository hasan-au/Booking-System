<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBreak extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeBreakFactory> */
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the employee that owns the EmployeeBreak
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function scopeForEmployeeOnDate(Builder $q, int $employeeId, Carbon $date): Builder
    {
        $weekday = (int)$date->dayOfWeek;

        return $q->where('employee_id',$employeeId)
            ->where(function($qq) use ($date, $weekday) {
                $qq->where(function($q1) use ($date) { // غير متكررة
                        $q1->where('is_recurring', false)
                           ->whereDate('date', $date->toDateString());
                    })
                   ->orWhere(function($q2) use ($weekday) { // أسبوعية
                        $q2->where('is_recurring', true)
                           ->where('weekday', $weekday);
                    });
            });
    }


}
