<?php

namespace App\Http\Requests;

use App\Rules\EmployeeProvidesService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // dd($this->all());
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'service_id' => ['required', 'integer','bail', 'exists:services,id',new EmployeeProvidesService((int) $this->input('employee_id'))],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_at' => ['required','date', 'date_format:Y-m-d H:i:s', 'after:now'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $v): void
    {
        $v->after(function ($v) {
            $date = $this->input('date');
            $time = $this->input('start_at');
            if (! $date || ! $time) return;

            $tz = config('app.timezone');
            $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date $time", $tz);

            if ($start->isPast()) {
                $v->errors()->add('start_at', 'The start time must be in the future.');
            }
        });
    }
}
