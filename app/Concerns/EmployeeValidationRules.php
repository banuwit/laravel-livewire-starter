<?php

namespace App\Concerns;

trait EmployeeValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function employeeRules(?int $employeeId = null): array
    {
        return [
            'employee_number' => array_filter([
                'nullable', 'string', 'max:50',
                $employeeId ? "unique:employees,employee_number,{$employeeId}" : 'unique:employees,employee_number',
            ]),
            'religion' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'address' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'employee_type' => ['nullable', 'in:permanent,contract,intern,parttime'],
            'join_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:join_date'],
        ];
    }
}
