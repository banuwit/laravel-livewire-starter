<?php

namespace App\Concerns;

trait ProfileDataValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function profileDataRules(?int $profileId = null): array
    {
        return [
            'identity_number' => array_filter([
                'nullable', 'string', 'max:50',
                $profileId ? "unique:profiles,identity_number,{$profileId}" : 'unique:profiles,identity_number',
            ]),
            'religion' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'address' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
        ];
    }
}
