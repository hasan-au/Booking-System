<?php

namespace App\Filament\Resources\EmployeeBreaks\Pages;

use App\Filament\Resources\EmployeeBreaks\EmployeeBreakResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeBreak extends EditRecord
{
    protected static string $resource = EmployeeBreakResource::class;

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
