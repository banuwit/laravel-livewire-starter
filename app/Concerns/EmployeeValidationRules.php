<?php

namespace App\Concerns;

trait EmployeeValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function employeeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female'],
            'phonenumber' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'address' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'is_active' => ['boolean'],
        ];
    }
}
