<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Bandung', 'code' => 'BD', 'province_id' => 1],
            ['name' => 'Bogor', 'code' => 'BG', 'province_id' => 1],
            ['name' => 'Bekasi', 'code' => 'BK', 'province_id' => 1],
            ['name' => 'Depok', 'code' => 'DP', 'province_id' => 1],
            ['name' => 'Jakarta Pusat', 'code' => 'JK', 'province_id' => 5],
            ['name' => 'Jakarta Utara', 'code' => 'JN', 'province_id' => 5],
            ['name' => 'Jakarta Selatan', 'code' => 'JS', 'province_id' => 5],
            ['name' => 'Jakarta Barat', 'code' => 'JB', 'province_id' => 5],
            ['name' => 'Jakarta Timur', 'code' => 'JT', 'province_id' => 5],
            ['name' => 'Surabaya', 'code' => 'SB', 'province_id' => 3],
            ['name' => 'Malang', 'code' => 'ML', 'province_id' => 3],
            ['name' => 'Semarang', 'code' => 'SM', 'province_id' => 2],
            ['name' => 'Solo', 'code' => 'SO', 'province_id' => 2],
            ['name' => 'Yogyakarta', 'code' => 'YO', 'province_id' => 6],
            ['name' => 'Medan', 'code' => 'MD', 'province_id' => 8],
            ['name' => 'Palembang', 'code' => 'PL', 'province_id' => 10],
            ['name' => 'Makassar', 'code' => 'MK', 'province_id' => 16],
            ['name' => 'Pontianak', 'code' => 'PT', 'province_id' => 11],
            ['name' => 'Samarinda', 'code' => 'SR', 'province_id' => 14],
            ['name' => 'Manado', 'code' => 'MN', 'province_id' => 17],
            ['name' => 'Banda Aceh', 'code' => 'BA', 'province_id' => 7],
            ['name' => 'Padang', 'code' => 'PD', 'province_id' => 9],
            ['name' => 'Jayapura', 'code' => 'JP', 'province_id' => 23],
            ['name' => 'Denpasar', 'code' => 'DS', 'province_id' => 1],
        ];

        foreach ($cities as $city) {
            City::factory()->create([
                'name' => $city['name'],
                'code' => $city['code'],
                'province_id' => $city['province_id'],
                'country_id' => 1,
            ]);
        }
    }
}
