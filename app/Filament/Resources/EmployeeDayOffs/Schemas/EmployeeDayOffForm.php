<?php

namespace App\Filament\Resources\EmployeeDayOffs\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmployeeDayOffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable(),
                Select::make('weekday')
                    ->options(collect(range(0, 6))
                        ->mapWithKeys(function ($i) {
                            $day = Carbon::now()
                                ->startOfWeek(Carbon::SUNDAY)
                                ->addDays($i);
                            return [$i => $day->isoFormat('dddd')];
                        })
                        ->toArray())
                    // ->formatStateUsing(function ($state) {
                    //     return Carbon::now()
                    //         ->startOfWeek()
                    //         ->addDays($state)
                    //         ->dayName;
                    // })
                    ->placeholder('Select a weekday'),
                DatePicker::make('date')
                    ->label('Specific Date (Optional)')
                    ->hint('Leave empty for recurring weekly day off. If specified, this will be a one-time day off on this specific date.')
                    ->minDate(Carbon::today())
                    ->format('Y-m-d')
                    ->rules(['nullable', 'date', 'after_or_equal:' . Carbon::today()->format('Y-m-d')])
                    ->placeholder('Select a specific date (optional)'),
                Textarea::make('reason')
                    ->columnSpanFull(),
            ]);
    }
}
