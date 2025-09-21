<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class EmployeeProvidesService implements ValidationRule
{
    public function __construct(private int $employeeId) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // dd($this->employeeId);
        $exists = DB::table('employee_service')
            ->where('employee_id', $this->employeeId)
            ->where('service_id',$value)
            ->exists();
        // dd($exists);
        if (!$exists) {
            $fail('This service is not provided by the selected employee.');
        }
    }
}
