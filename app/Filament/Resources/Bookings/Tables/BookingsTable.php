<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\SelectColumn;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('employee.name')
                    ->searchable(),
                TextColumn::make('service.name')
                    ->searchable(),
                ColumnGroup::make('Customer Details')
                    ->columns([
                        TextColumn::make('customer_name')
                            ->searchable(),
                        TextColumn::make('customer_phone')
                            ->searchable(),
                        TextColumn::make('customer_email')
                            ->searchable(),
                    ]),
                ColumnGroup::make('Booking Date & Time')
                        ->columns([
                    TextColumn::make('start_at')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('end_at')
                        ->dateTime()
                        ->sortable(),
                ]),
                SelectColumn::make('status')
                    ->options(BookingStatus::class)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label(''),
                EditAction::make()->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
