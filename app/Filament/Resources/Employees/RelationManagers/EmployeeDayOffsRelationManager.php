<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Resources\EmployeeDayOffs\EmployeeDayOffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class EmployeeDayOffsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeDayOffs';

    protected static ?string $relatedResource = EmployeeDayOffResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
