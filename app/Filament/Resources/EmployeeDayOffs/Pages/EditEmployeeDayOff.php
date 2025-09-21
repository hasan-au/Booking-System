<?php

namespace App\Filament\Resources\EmployeeDayOffs\Pages;

use App\Filament\Resources\EmployeeDayOffs\EmployeeDayOffResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeDayOff extends EditRecord
{
    protected static string $resource = EmployeeDayOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
