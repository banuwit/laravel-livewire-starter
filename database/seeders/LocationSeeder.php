<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['code' => 'ID'],
            ['name' => 'Indonesia']
        );

        $provinces = [
            'Aceh' => 'AC',
            'Sumatera Utara' => 'SU',
            'Sumatera Barat' => 'SB',
            'Riau' => 'RI',
            'Jambi' => 'JA',
            'Sumatera Selatan' => 'SS',
            'Lampung' => 'LA',
            'Kepulauan Bangka Belitung' => 'BB',
            'Kepulauan Riau' => 'KR',
            'DKI Jakarta' => 'JK',
            'Jawa Barat' => 'JB',
            'Jawa Tengah' => 'JT',
            'DI Yogyakarta' => 'YO',
            'Jawa Timur' => 'JI',
            'Banten' => 'BT',
            'Bali' => 'BA',
            'Nusa Tenggara Barat' => 'NB',
            'Nusa Tenggara Timur' => 'NT',
            'Kalimantan Barat' => 'KB',
            'Kalimantan Tengah' => 'KT',
            'Kalimantan Selatan' => 'KS',
            'Kalimantan Timur' => 'KI',
            'Kalimantan Utara' => 'KU',
            'Sulawesi Utara' => 'SN',
            'Sulawesi Tengah' => 'ST',
            'Sulawesi Selatan' => 'SS',
            'Sulawesi Tenggara' => 'SG',
            'Gorontalo' => 'GO',
            'Sulawesi Barat' => 'SW',
            'Maluku' => 'MA',
            'Maluku Utara' => 'MU',
            'Papua' => 'PA',
            'Papua Barat' => 'PB',
            'Papua Barat Daya' => 'PD',
            'Papua Tengah' => 'PT',
            'Papua Pegunungan' => 'PP',
            'Papua Selatan' => 'PS',
        ];

        $provinceMap = [];
        foreach ($provinces as $name => $code) {
            $province = Province::firstOrCreate(
                ['name' => $name],
                ['code' => $code, 'country_id' => $country->id]
            );
            $provinceMap[$name] = $province->id;
        }

        $cities = [
            'Banda Aceh' => 'Aceh',
            'Medan' => 'Sumatera Utara',
            'Padang' => 'Sumatera Barat',
            'Pekanbaru' => 'Riau',
            'Jambi' => 'Jambi',
            'Palembang' => 'Sumatera Selatan',
            'Bandar Lampung' => 'Lampung',
            'Pangkal Pinang' => 'Kepulauan Bangka Belitung',
            'Tanjung Pinang' => 'Kepulauan Riau',
            'Jakarta' => 'DKI Jakarta',
            'Bandung' => 'Jawa Barat',
            'Semarang' => 'Jawa Tengah',
            'Yogyakarta' => 'DI Yogyakarta',
            'Surabaya' => 'Jawa Timur',
            'Serang' => 'Banten',
            'Denpasar' => 'Bali',
            'Mataram' => 'Nusa Tenggara Barat',
            'Kupang' => 'Nusa Tenggara Timur',
            'Pontianak' => 'Kalimantan Barat',
            'Palangkaraya' => 'Kalimantan Tengah',
            'Banjarmasin' => 'Kalimantan Selatan',
            'Samarinda' => 'Kalimantan Timur',
            'Tanjung Selor' => 'Kalimantan Utara',
            'Manado' => 'Sulawesi Utara',
            'Palu' => 'Sulawesi Tengah',
            'Makassar' => 'Sulawesi Selatan',
            'Kendari' => 'Sulawesi Tenggara',
            'Gorontalo' => 'Gorontalo',
            'Mamuju' => 'Sulawesi Barat',
            'Ambon' => 'Maluku',
            'Ternate' => 'Maluku Utara',
            'Jayapura' => 'Papua',
            'Manokwari' => 'Papua Barat',
            'Sorong' => 'Papua Barat',
            'Timika' => 'Papua',
            'Wamena' => 'Papua Pegunungan',
            'Merauke' => 'Papua Selatan',
        ];

        foreach ($cities as $cityName => $provinceName) {
            $provinceId = $provinceMap[$provinceName] ?? null;
            if ($provinceId) {
                City::firstOrCreate(
                    ['name' => $cityName, 'province_id' => $provinceId],
                    ['code' => null, 'country_id' => $country->id]
                );
            }
        }
    }
}
