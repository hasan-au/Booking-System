<?php

namespace App\Models;

use App\Enums\BookingStatus ;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status'   => BookingStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public static function blockingStatuses(): array
    {
        return ['confirmed','in_progress','pending'];
    }

    public function scopeOverlap(Builder $q, int $employeeId, $startAt, $endAt): Builder
    {
        return $q->where('employee_id', $employeeId)
                 ->whereIn('status', self::blockingStatuses())
                 ->where('start_at', '<', $endAt)
                 ->where('end_at', '>', $startAt);
    }
}
