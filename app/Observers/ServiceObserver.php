<?php

namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Str;
class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function creating(Service $service): void
    {
        $originalSlug = Str::slug($service->name);
        $slug = $originalSlug;
        $counter = 1;

        while (Service::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $service->slug = $slug;
    }

    public function created(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "updated" event.
     */
    public function updating(Service $service): void
    {
        if ($service->isDirty('name')) {
            $originalSlug = Str::slug($service->name);
            $slug = $originalSlug;
            $counter = 1;

            while (Service::where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $service->slug = $slug;
        }
    }
    public function updated(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        //
    }
}
