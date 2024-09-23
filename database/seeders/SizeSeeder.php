<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
public static function generateType(){

    $types = ['clothing','other'];

   
    return  $types[rand(0,1)];
}

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $sizes_array =[
            [
                'value' => ['ar' => 'S', 'en' => 'S'],
                'sku_code' => 10,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => 'M', 'en' => 'M'],
                'sku_code' => 11,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => 'L', 'en' => 'L'],
                'sku_code' => 12,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => 'XL', 'en' => 'XL'],
                'sku_code' => 13,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => 'XXL', 'en' => 'XXL'],
                'sku_code' => 14,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '32', 'en' => '32'],
                'sku_code' => 15,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '34', 'en' => '34'],
                'sku_code' => 16,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '36', 'en' => '36'],
                'sku_code' => 17,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '38', 'en' => '38'],
                'sku_code' => 18,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '40', 'en' => '40'],
                'sku_code' => 19,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '42', 'en' => '42'],
                'sku_code' => 20,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '44', 'en' => '44'],
                'sku_code' => 21,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '46', 'en' => '46'],
                'sku_code' => 22,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '48', 'en' => '48'],
                'sku_code' => 23,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '50', 'en' => '50'],
                'sku_code' => 24,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '52', 'en' => '52'],
                'sku_code' => 25,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '54', 'en' => '54'],
                'sku_code' => 26,
                'type' => self::generateType()
            ],

            [
                'value' => ['ar' => '1', 'en' => '1'],
                'sku_code' => 27,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '2', 'en' => '2'],
                'sku_code' => 28,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '3', 'en' => '3'],
                'sku_code' => 29,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '4', 'en' => '4'],
                'sku_code' => 30,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '6', 'en' => '6'],
                'sku_code' => 31,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '8', 'en' => '8'],
                'sku_code' => 32,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '10', 'en' => '10'],
                'sku_code' => 33,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '12', 'en' => '12'],
                'sku_code' => 34,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '14', 'en' => '14'],
                'sku_code' => 35,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '6 أشهر', 'en' => '6m'],
                'sku_code' => 36,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '12 شهراً', 'en' => '12m'],
                'sku_code' => 37,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '18 شهراً', 'en' => '18m'],
                'sku_code' => 38,
                'type' => self::generateType()
            ],
            [
                'value' => ['ar' => '24 شهراً', 'en' => '24m'],
                'sku_code' => 39,
                'type' => self::generateType()
            ],
            
        ];
        foreach ($sizes_array as $size) {
            Size::create($size);
        }
    }
}
