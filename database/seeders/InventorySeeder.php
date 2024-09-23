<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inventory::factory(5)->create();
        // $faker = Faker::create();
        // // $faker_en = Faker::create('en_EN');

        // // Lattakia
        // $country = $faker->randomElement(['DW', 'LW', 'AW']);
        // $warehouse_number = $faker->randomDigit();

        // return [
        //     'name' => $this->faker->name() ,
        //     'city' => $this->faker->city() ,
        //     'code' => $faker->randomElement(['DW', 'LW', 'AW']);,
        // ];
        Inventory::create([
            'name' => 'Lattakia Warehouse',
            'city' => 'اللاذقية',
            'city_id' => 1,
            'lat' => 35.5167,
            'long' => 35.7833,
            'code' => 'LW1'
        ]);

        Inventory::create([
            'name' => 'Aleppo Warehouse',
            'city' => 'حلب',
            'city_id' => 2,
            'lat' => 36.2167,
            'long' => 37.1667,
            'code' => 'AW2'
        ]);

        Inventory::create([
            'name' => 'Damascus Warehouse',
            'city' => 'دمشق',
            'city_id' => 3,
            'lat' => 33.5102,
            'long' => 36.2913,
            'code' => 'DW3'
        ]);

        Inventory::create([
            'name' => 'Homs Warehouse',
            'city' => 'حمص',
            'city_id' => 4,
            'lat' => 34.7333,
            'long' => 36.7167,
            'code' => 'HW4'
        ]);

        // Inventory::create([
        //     'name' => [
        //         'en' => 'Lattakia Warehouse',
        //         'ar' => 'مستودع اللاذقية',
        //     'address' => [
        //         'en' => $faker_ar->address() ,
        //         'ar' => $faker_en->address()
        //     ] ,
        //     'city' => [
        //         'en' => 'lattkaia',
        //         'ar' => 'اللاذقية',
        //     ] ,
        //     'code' => "LW2" ,
        // ]);

        // // Aleppo
        // Inventory::create([
        //     'name' => [
        //         'en' => 'Aleppo Warehouse',
        //         'ar' => 'مستودع حلب',
        //     ] ,
        //     'address' => [
        //         'en' => $faker_ar->address() ,
        //         'ar' => $faker_en->address()
        //     ] ,
        //     'city' => [
        //         'en' => 'Aleppo',
        //         'ar' => 'حلب',
        //     ] ,
        //     'code' => "AW1" ,
        // ]);

        // Inventory::create([
        //     'name' => [
        //         'en' => 'Aleppo Warehouse',
        //         'ar' => 'مستودع حلب',
        //     ] ,
        //     'address' => [
        //         'en' => $faker_ar->address() ,
        //         'ar' => $faker_en->address()
        //     ] ,
        //     'city' => [
        //         'en' => 'Aleppo',
        //         'ar' => 'حلب',
        //     ] ,
        //     'code' => "AW2" ,
        // ]);

        // // Damascus
        // Inventory::create([
        //     'name' => [
        //         'en' => 'Damascus Warehouse',
        //         'ar' => 'مستودع دمشق',
        //     ] ,
        //     'address' => [
        //         'en' => $faker_ar->address() ,
        //         'ar' => $faker_en->address()
        //     ] ,
        //     'city' => [
        //         'en' => 'Damascus',
        //         'ar' => 'دمشق',
        //     ] ,
        //     'code' => "DW1" ,
        // ]);

        // Inventory::create([
        //     'name' => [
        //         'en' => 'Damascus Warehouse',
        //         'ar' => 'مستودع دمشق',
        //     ] ,
        //     'address' => [
        //         'en' => $faker_ar->address() ,
        //         'ar' => $faker_en->address()
        //     ] ,
        //     'city' => [
        //         'en' => 'Damascus',
        //         'ar' => 'دمشق',
        //     ] ,
        //     'code' => "DW2" ,
        // ]);


    }
}
