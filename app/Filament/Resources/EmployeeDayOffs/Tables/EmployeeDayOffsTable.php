<?php

namespace App\Filament\Resources\EmployeeDayOffs\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeDayOffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable(),
                TextColumn::make('weekday')
                    ->label('Day')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays($state)->dayName;
                    }),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
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
                EditAction::make()->label('')->icon('heroicon-o-pencil'),
                DeleteAction::make()->label('')->icon('heroicon-o-trash'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
