<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use App\Enums\BookingStatus;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('employee.name')
                    ->label('Employee')
                    ->placeholder('-'),
                TextEntry::make('service.name')
                    ->label('Service')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('customer_name'),
                TextEntry::make('customer_phone')
                    ->placeholder('-'),
                TextEntry::make('customer_email')
                    ->placeholder('-'),
                TextEntry::make('start_at')
                    ->dateTime(),
                TextEntry::make('end_at')
                    ->dateTime(),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BookingStatus ? $state->label() : BookingStatus::tryFrom($state)?->label() ?? 'Unknown')
                    ->color(fn ($state) => $state instanceof BookingStatus ? $state->color() : BookingStatus::tryFrom($state)?->color() ?? 'gray'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
