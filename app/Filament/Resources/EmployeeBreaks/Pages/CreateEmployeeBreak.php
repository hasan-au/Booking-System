<?php

namespace App\Filament\Resources\EmployeeBreaks\Pages;

use App\Filament\Resources\EmployeeBreaks\EmployeeBreakResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeBreak extends CreateRecord
{
    protected static string $resource = EmployeeBreakResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
