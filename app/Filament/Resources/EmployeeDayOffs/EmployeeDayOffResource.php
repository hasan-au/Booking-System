<?php

namespace App\Filament\Resources\EmployeeDayOffs;

use App\Filament\Resources\EmployeeDayOffs\Pages\CreateEmployeeDayOff;
use App\Filament\Resources\EmployeeDayOffs\Pages\EditEmployeeDayOff;
use App\Filament\Resources\EmployeeDayOffs\Pages\ListEmployeeDayOffs;
use App\Filament\Resources\EmployeeDayOffs\Schemas\EmployeeDayOffForm;
use App\Filament\Resources\EmployeeDayOffs\Tables\EmployeeDayOffsTable;
use App\Models\EmployeeDayOff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeDayOffResource extends Resource
{
    protected static ?string $model = EmployeeDayOff::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    public static function form(Schema $schema): Schema
    {
        return EmployeeDayOffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeDayOffsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeDayOffs::route('/'),
            'create' => CreateEmployeeDayOff::route('/create'),
            'edit' => EditEmployeeDayOff::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('employee');
    }


}
