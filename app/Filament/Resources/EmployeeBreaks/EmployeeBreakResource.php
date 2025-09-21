<?php

namespace App\Filament\Resources\EmployeeBreaks;

use App\Filament\Resources\EmployeeBreaks\Pages\CreateEmployeeBreak;
use App\Filament\Resources\EmployeeBreaks\Pages\EditEmployeeBreak;
use App\Filament\Resources\EmployeeBreaks\Pages\ListEmployeeBreaks;
use App\Filament\Resources\EmployeeBreaks\Schemas\EmployeeBreakForm;
use App\Filament\Resources\EmployeeBreaks\Tables\EmployeeBreaksTable;
use App\Models\EmployeeBreak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeBreakResource extends Resource
{
    protected static ?string $model = EmployeeBreak::class;

    protected static ?int $navigationSort = 4;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    public static function form(Schema $schema): Schema
    {
        return EmployeeBreakForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeBreaksTable::configure($table);
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
            'index' => ListEmployeeBreaks::route('/'),
            'create' => CreateEmployeeBreak::route('/create'),
            'edit' => EditEmployeeBreak::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('employee');
    }

}
