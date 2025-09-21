<?php

namespace App\Filament\Resources\Employees\Schemas;

use Dom\Text;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
// use Filament\Forms\Schema;
use Illuminate\Validation\Rule;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\Facades\Storage;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->required()
                    ->tel(),
                Select::make('services')
                    ->relationship('services', 'name')
                    ->preload()
                    ->maxItems(3)
                    ->required()
                    ->rules(['array',Rule::exists('services', 'id')])
                    ->multiple(),
                TimePicker::make('work_start_time')
                    ->label('Work Start Time')
                    ->time()
                    ->seconds(false)
                    ->default('09:00')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $component, $get, $set) {
                        // Trigger validation of end time by getting and setting its value
                        $endTime = $get('work_end_time');
                        if ($endTime) {
                            $set('work_end_time', $endTime);
                        }
                    })
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $endTime = $get('work_end_time');

                                if (!$endTime || !$value) {
                                    return;
                                }

                                // Convert time strings to comparable format
                                $startTimestamp = strtotime($value);
                                $endTimestamp = strtotime($endTime);

                                if ($startTimestamp >= $endTimestamp) {
                                    $fail('Start time must be before end time.');
                                }
                            };
                        }
                    ]),
                TimePicker::make('work_end_time')
                    ->label('Work End Time')
                    ->time()
                    ->seconds(false)
                    ->default('17:00')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $component, $get, $set) {
                        // Trigger validation of start time by getting and setting its value
                        $startTime = $get('work_start_time');
                        if ($startTime) {
                            $set('work_start_time', $startTime);
                        }
                    })
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $startTime = $get('work_start_time');

                                if (!$startTime || !$value) {
                                    return;
                                }

                                // Convert time strings to comparable format
                                $startTimestamp = strtotime($startTime);
                                $endTimestamp = strtotime($value);

                                if ($endTimestamp <= $startTimestamp) {
                                    $fail('End time must be after start time.');
                                }

                                // Optional: Add minimum work duration validation
                                $diffMinutes = ($endTimestamp - $startTimestamp) / 60;
                                if ($diffMinutes < 30) {
                                    $fail('Work duration must be at least 30 minutes.');
                                }
                            };
                        }
                    ]),

                FileUpload::make('photo')
                ->label('Photo')
                ->image()
                ->imageEditor()
                ->directory('employee-thumbnails')
                ->disk('public')
                ->imagePreviewHeight('100')
                ->loadingIndicatorPosition('left')
                ->panelLayout('compact'),
                TextInput::make('rating')
                ->label('Rate')
                ->numeric()
                ->step('0.1')
                ->default(4.5)
                ->required()
                ->rules([
                    'numeric',
                    'min:1',
                    'max:5',
                ])

            ]);
    }
}
