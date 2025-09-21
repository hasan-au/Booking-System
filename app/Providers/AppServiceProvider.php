<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentTimezone;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Booking::observe(\App\Observers\BookingObserver::class);
        Service::observe(\App\Observers\ServiceObserver::class);
        // FilamentTimezone::set('Australia/Melbourne');
    }
}
