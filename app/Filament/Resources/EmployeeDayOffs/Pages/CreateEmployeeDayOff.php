<?php

namespace App\Filament\Resources\EmployeeDayOffs\Pages;

use App\Filament\Resources\EmployeeDayOffs\EmployeeDayOffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeDayOff extends CreateRecord
{
    protected static string $resource = EmployeeDayOffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
