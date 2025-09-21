<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Service;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Dom\Text;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rule;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([

                    Step::make('Service & Employee')
                        ->schema([
                            Select::make('employee_id')->searchable()->live()->label('Employee')
                                ->options(Employee::pluck('name', 'id')->toArray())
                                ->rules([
                                        'integer',
                                        Rule::exists('employees', 'id'),
                                    ])
                                ->afterStateUpdated(function (Set $set) {
                                        $set('service_id', null);
                                        $set('date', null);
                                        $set('start_at', null);
                                    }),
                            Select::make('service_id')->label('Service')->live()
                                ->options(function (Get $get) {
                                    $employeeId = $get('employee_id');

                                    if (! filled($employeeId)) {
                                        return [];
                                    }
                                    return Service::query()
                                        ->whereHas('employees', fn ($q) => $q->whereKey($employeeId))
                                        ->where('status', 'active')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->afterStateUpdated(function (Set $set) {
                                        $set('date', null);
                                        $set('start_at', null);
                                    })

                                ->disabled(fn (Get $get) => ! filled($get('employee_id')))
                                ->placeholder('Select an employee first')
                                ->searchable()
                                ->rules(fn (Get $get) => array_filter([
                                    'integer',
                                    Rule::exists('services', 'id'),
                                    filled($get('employee_id'))
                                        ? Rule::exists('employee_service', 'service_id')
                                            ->where(fn ($q) => $q->where('employee_id', $get('employee_id')))
                                        : null,
                                ]))
                        ])->icon('heroicon-o-briefcase')->description('Select the service and employee for the booking')->columns(2),
                    Step::make('Customer Details')
                        ->schema([
                            TextInput::make('customer_name')->required(),
                            TextInput::make('customer_phone')->tel(),
                            TextInput::make('customer_email')->email(),
                        ])->icon('heroicon-o-user')->description('Enter the customer details')->columns(2),
                    Step::make('Booking Details')
                        ->schema([
                            TextInput::make('booking_date_display')
                            ->label('Booking Date')->disabled()->dehydrated(false)->columnSpanFull()
                            ->afterStateHydrated(function (TextInput $component, ?Booking $record) {
                                    if ($record && $record->start_at) {

                                        $component->state(
                                            Carbon::parse($record->start_at)->format('Y-m-d H:i') . '-' . Carbon::parse($record->end_at)->format('H:i')
                                        );
                                    }
                                })->hidden(fn (string $operation): bool => in_array($operation, ['create', 'view'])),
                            DateTimePicker::make('date')->live()->label('Booking Date')
                                ->format('Y-m-d')
                                ->displayFormat('Y-m-d')
                                ->withoutTime()
                                ->native(false)
                                ->closeOnDateSelection()
                                // ->disabledDates(fn (Get $get) => Employee::find($get('employee_id'))?->getAllDaysOffAsDate() ?? [])
                                ->disabledDates(function (Get $get) {
                                    $employeeId = $get('employee_id');
                                    if (! filled($employeeId)) {
                                        return [];
                                    }

                                    $employee = \App\Models\Employee::find($employeeId);
                                    if (! $employee) {
                                        return [];
                                    }

                                    $dates = $employee->getAllDaysOffAsDate() ?? [];

                                    // المفتاح: شيل أي null / قيم مش صالحة وحوّل لصيغة متسقة
                                    return collect($dates)
                                        ->filter(fn ($d) => filled($d))
                                        ->map(function ($d) {
                                            // اقبل Carbon أو سترنغ
                                            try {
                                                return \Carbon\Carbon::parse($d)->format('Y-m-d');
                                            } catch (\Throwable $e) {
                                                return null;
                                            }
                                        })
                                        ->filter()
                                        ->unique()
                                        ->values()
                                        ->all();
                                })

                                ->minDate(now()->startOfDay())
                                ->required(),
                            Select::make('start_at')
                                ->label('Booking Time')
                                ->required()
                                ->disabled(fn (Get $get) => ! filled($get('date')))
                                ->options(function (Get $get,string $operation) {

                                    $employeeId = $get('employee_id');
                                    $serviceId = $get('service_id');
                                    $date = $get('date');

                                    if (! filled($employeeId) || ! filled($serviceId) || ! filled($date)) {
                                        return [];
                                    }

                                    $employee = Employee::find($employeeId);
                                    $service = Service::find($serviceId);
                                    if (! $employee || ! $service) {
                                        return [];
                                    }
                                    $slots = [];
                                    if ($operation === 'create') {
                                        $slots = app(AvailabilityService::class)->getSlotsForDate(
                                            employee: $employee,
                                            service: $service,
                                            date: Carbon::createFromFormat('Y-m-d', $date, config('app.timezone')),
                                            slotStepMinutes: 15,
                                            bufferBefore: 0,
                                            bufferAfter: 0,
                                            leadMinutesIfToday: 5,
                                        );
                                    }elseif ($operation === 'edit') {
                                        $bookingId = $get('id');
                                        $slots = app(AvailabilityService::class)->getSlotsForUpdateBooking(
                                            booking: Booking::find($bookingId),
                                            employee: $employee,
                                            service: $service,
                                            date: Carbon::createFromFormat('Y-m-d', $date, config('app.timezone')),
                                            slotStepMinutes: 15,
                                            bufferBefore: 0,
                                            bufferAfter: 0,
                                            leadMinutesIfToday: 5,
                                        );
                                    }

                                    return collect($slots)->mapWithKeys(fn ($s) => [$s=>$s])
                                        ->unique()
                                        ->all();
                                })
                                ->placeholder('Select a date first'),
                            Select::make('status')->options(BookingStatus::class)->default('confirmed')->required(),
                        ])->icon('heroicon-o-calendar')->description('Set the booking date, time, and status')->columns(2),
                ])->columnSpanFull()
                // ->completedIcon(Heroicon::HandThumbUp),
            ]);
    }
}
