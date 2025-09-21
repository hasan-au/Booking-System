<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'duration_minutes' => 'integer',
        'status'=> ServiceStatus::class,
        'price' => 'integer',
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => (int) round(((float) $value) * 100),
        );
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_service', 'service_id', 'employee_id');
    }


}
