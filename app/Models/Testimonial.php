<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonialFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'service_id',
        'rating',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    protected $casts = [
        'rating' => 'integer',
    ];
}
