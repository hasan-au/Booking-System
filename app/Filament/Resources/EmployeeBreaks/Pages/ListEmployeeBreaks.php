<?php

namespace App\Filament\Resources\EmployeeBreaks\Pages;

use App\Filament\Resources\EmployeeBreaks\EmployeeBreakResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeBreaks extends ListRecords
{
    protected static string $resource = EmployeeBreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
