<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Http\Requests\BookingUpdateRequest;
use App\Models\Employee;
use App\Models\Service;
use App\Services\BookingService;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    // protected function afterFill(): void
    // {
    //     $record = $this->record;

    //     if ($record?->start_at) {
    //         $this->form->fill([
    //             'date' => $record->start_at->format('Y-m-d'),
    //         ]);
    //     }
    // }
    protected function mutateFormDataBeforeSave(array $data): array
    {

        $start = Carbon::parse($data['start_at']);


        $service = Service::findOrFail($data['service_id']);
        $duration = (int) ($service->duration_minutes ?? 0);


        $data['end_at'] = $start->copy()->addMinutes($duration)->format('Y-m-d H:i:s');


        if ($duration === 0) {
            $data['end_at'] = $start->copy()->addHour()->format('Y-m-d H:i:s');
        }

        unset($data['date']);

        return $data;
    }

    protected function afterValidate(): void
    {
        // Get form data
        $data = $this->form->getState();
        $payload = $this->normalizeForRequest($data);

        // Create validator
        $request = new BookingUpdateRequest();
        if (method_exists($request, 'authorize') && ! $request->authorize()) {
            abort(403);
        }
        if (method_exists($request, 'replace')) {
            $request->replace($payload);
        }
        $validator = validator($payload, $request->rules(), $request->messages());

        if ($validator->fails()) {

            Notification::make()
                ->title('Validation Failed')
                ->body('Please check the form for errors: ' . implode(', ', $validator->errors()->all()))
                ->danger()
                ->send();

            throw new ValidationException($validator);
        }
    }


    private function normalizeForRequest(array $data): array
    {
        foreach ($data as $key => $value) {
            // لو Array: طبّعها بشكل recursive
            if (is_array($value)) {
                $data[$key] = $this->normalizeForRequest($value);
                continue;
            }

            // Enums
            if ($value instanceof \BackedEnum) {
                $data[$key] = $value->value; // enum مدعوم بقيمة
                continue;
            }
            if ($value instanceof \UnitEnum) {
                $data[$key] = $value->name; // enum غير مدعوم بقيمة
                continue;
            }


            // if ($value instanceof \DateTimeInterface) {
            //     if ($key === 'date') {
            //         $data[$key] = $value->format('Y-m-d');
            //     } elseif (in_array($key, ['start_at'], true)) {
            //         $data[$key] = $value->format('H:i');
            //     } else {
            //         $data[$key] = \Carbon\Carbon::parse($value)->toDateTimeString();
            //     }
            // }

            // أي Object يقدر ينطبع كنص
            // if (is_object($value) && method_exists($value, '__toString')) {
            //     $data[$key] = (string) $value;
            //     continue;
            // }

        }

        return $data;
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $employee = Employee::findOrFail($data['employee_id']);
        $service  = Service::findOrFail($data['service_id']);
        $startAt  = Carbon::parse($data['start_at']);

        $customer = [
            'name'  => $data['customer_name'],
            'phone' => $data['customer_phone'] ?? null,
            'email' => $data['customer_email'] ?? null,
        ];

        $status = $data['status'] ?? null; // ممكن يكون Enum أو string

        try {
            return app(BookingService::class)->update(
                booking : $record,
                employee: $employee,
                service : $service,
                startAt : $startAt,
                customer: $customer,
                status  : $status
            );
        } catch (\RuntimeException $e) {
            Notification::make()->title('Cannot update booking')->body($e->getMessage())->danger()->send();

            throw ValidationException::withMessages([
                'start_at' => $e->getMessage(),
            ]);
        }
    }
}
