<?php

namespace App\Filament\Resources\EmployeeDayOffs\Pages;

use App\Filament\Resources\EmployeeDayOffs\EmployeeDayOffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeDayOffs extends ListRecords
{
    protected static string $resource = EmployeeDayOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
