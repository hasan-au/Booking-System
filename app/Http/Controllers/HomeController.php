<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Testimonial;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $employees = Employee::with([
            'services' => function ($query) {$query->where('status', 'active');},
            ])->get();
        $employees->each(function ($employee) {
            $employee->setAttribute('all_days_off_as_date', $employee->getAllDaysOffAsDate());
            $employee->setAttribute('weekly_days_off', $employee->employeeDayOffs()->whereNotNull('weekday')->pluck('weekday')->toArray());
            $employee->photo = $employee->photo ? asset('storage/' . $employee->photo) : null;
        });


        $services = Service::where('status', 'active')->whereHas('employees')->with('employees')->get();
        // Add all_days_off_as_date attribute to each employee in services
        $services->each(function ($service) {
            $service->employees->each(function ($employee) {
                $employee->setAttribute('all_days_off_as_date', $employee->getAllDaysOffAsDate());
                $employee->photo = $employee->photo ? asset('storage/' . $employee->photo) : null;
            });
        });

        $testimonials = Testimonial::with('service')->latest()->take(3)->get();

        return Inertia::render('Home', [
            'employees' => $employees,
            'services' => $services,
            'minDate' => Carbon::now()->format('Y-m-d'),
            'maxDate' => Carbon::now()->addMonth()->format('Y-m-d'),
            'testimonials' => $testimonials,
        ]);

    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        // dd($request->all());

        $service = Service::where('status', 'active')->find($request->service_id);
        $employee = Employee::find($request->employee_id);
        $date = Carbon::parse($request->date)->setTimezone(config('app.timezone'));
        // dd($date);
        $availabilityService = new AvailabilityService();
        $availableSlots = $availabilityService->getSlotsForDate($employee, $service, $date);

        return response()->json([
            'available_slots' => $availableSlots,
        ]);
    }

    public function bookAppointment(Request $request)
    {

        $request->validate([
            'step1.service_id' => 'required|exists:services,id',
            'step2.employee_id' => 'required|exists:employees,id',
            'step3.date' => 'required|date',
            'step3.datetime' => 'required|string',
            'step4.customer_name' => 'required|string|max:255',
            'step4.customer_email' => 'required|email|max:255',
            'step4.customer_phone' => 'required|string|max:20',
        ]);



        $service = Service::where('status', 'active')->find($request->step1['service_id']);
        $employee = Employee::find($request->step2['employee_id']);
        $date = Carbon::parse($request->step3['date'])->setTimezone(config('app.timezone'));
        $time = $request->step3['datetime'];
        // dd($date, $time);
        $availabilityService = new AvailabilityService();
        $availableSlots = $availabilityService->getSlotsForDate($employee, $service, $date);

        if (!in_array($time, $availableSlots)) {
            return redirect()->back()->withErrors(['time' => 'The selected time slot is no longer available. Please choose a different time.'])->withInput();
        }

        // Create the booking
        $booking = app(BookingService::class)->create(
            $employee,
            $service,
            Carbon::parse($time)->setTimezone(config('app.timezone')),
            [
                'name' => $request->step4['customer_name'],
                'email' => $request->step4['customer_email'],
                'phone' => $request->step4['customer_phone'],
            ],
        );

        // Send notification emails (to be implemented)
        Notification::route('mail', $booking->customer_email)->notify(new \App\Notifications\CustomerBookingNotification($booking));
        Notification::route('mail', config('mail.admin_email'))->notify((new \App\Notifications\AdminBookingNotification($booking))->delay(now()->addSeconds(60)));

        return redirect()->route('home');
    }
}
