<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'religion' => [
                ['code' => 'islam',              'value' => 'Islam'],
                ['code' => 'kristen_protestan',  'value' => 'Kristen Protestan'],
                ['code' => 'kristen_katolik',    'value' => 'Kristen Katolik'],
                ['code' => 'hindu',              'value' => 'Hindu'],
                ['code' => 'buddha',             'value' => 'Buddha'],
                ['code' => 'konghucu',           'value' => 'Konghucu'],
                ['code' => 'other',              'value' => 'Lainnya'],
            ],
            'marital_status' => [
                ['code' => 'single',   'value' => 'Single'],
                ['code' => 'married',  'value' => 'Married'],
                ['code' => 'divorced', 'value' => 'Divorced'],
                ['code' => 'widowed',  'value' => 'Widowed'],
            ],
        ];

        foreach ($groups as $group => $items) {
            foreach ($items as $sort => $item) {
                Parameter::firstOrCreate(
                    ['code' => $item['code']],
                    [
                        'group' => $group,
                        'value' => $item['value'],
                        'is_system' => true,
                        'is_active' => true,
                        'sort_order' => $sort + 1,
                    ]
                );
            }
        }
    }
}
