<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        City::create([
            'name' => [
                'en' => 'Damascus',
                'ar' => 'دمشق',
            ],
        ]);

        City::create([
            'name' => [
                'en' => 'Rif Dimashq',
                'ar' => 'ريف دمشق',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Aleppo',
                'ar' => 'حلب',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Homs',
                'ar' => 'حمص',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Hama',
                'ar' => 'حماة',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Latakia',
                'ar' => 'اللاذقية',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Tartus',
                'ar' => 'طرطوس',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Daraa',
                'ar' => 'درعا',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'As-Suwayda',
                'ar' => 'السويداء',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Al-Quneitra',
                'ar' => 'القنيطرة',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Deir ez-Zor',
                'ar' => 'دير الزور',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Ar-Raqqah',
                'ar' => 'الرقة',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Al-Hasakah',
                'ar' => 'الحسكة',
            ],
        ]);


        City::create([
            'name' => [
                'en' => 'Idlib',
                'ar' => 'إدلب',
            ],
        ]);

        // Add more cities as needed
    }
}

