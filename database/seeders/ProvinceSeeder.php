<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ['name' => 'Jawa Barat', 'code' => 'JB'],
            ['name' => 'Jawa Tengah', 'code' => 'JT'],
            ['name' => 'Jawa Timur', 'code' => 'JU'],
            ['name' => 'Banten', 'code' => 'BT'],
            ['name' => 'DKI Jakarta', 'code' => 'JK'],
            ['name' => 'DI Yogyakarta', 'code' => 'YO'],
            ['name' => 'Aceh', 'code' => 'AC'],
            ['name' => 'Sumatera Utara', 'code' => 'SU'],
            ['name' => 'Sumatera Barat', 'code' => 'SB'],
            ['name' => 'Sumatera Selatan', 'code' => 'SS'],
            ['name' => 'Sumatera Timur', 'code' => 'ST'],
            ['name' => 'Kalimantan Barat', 'code' => 'KB'],
            ['name' => 'Kalimantan Selatan', 'code' => 'KS'],
            ['name' => 'Kalimantan Timur', 'code' => 'KI'],
            ['name' => 'Kalimantan Utara', 'code' => 'KU'],
            ['name' => 'Sulawesi Selatan', 'code' => 'SN'],
            ['name' => 'Sulawesi Utara', 'code' => 'SA'],
            ['name' => 'Sulawesi Tenggara', 'code' => 'SG'],
            ['name' => 'Sulawesi Barat', 'code' => 'SW'],
            ['name' => 'Sulawesi Tengah', 'code' => 'ST'],
            ['name' => 'Maluku', 'code' => 'ML'],
            ['name' => 'Maluku Utara', 'code' => 'MU'],
            ['name' => 'Papua', 'code' => 'PA'],
            ['name' => 'Papua Barat', 'code' => 'PB'],
        ];

        foreach ($provinces as $province) {
            Province::factory()->create([
                'name' => $province['name'],
                'code' => $province['code'],
                'country_id' => 1,
            ]);
        }
    }
}
        
            
