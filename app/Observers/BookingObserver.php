<?php

namespace App\Observers;

use App\Models\Booking;
use Illuminate\Support\Str;
class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function creating(Booking $booking): void
    {
        $booking->uuid = Str::uuid();
    }
    public function created(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        //
    }
}
