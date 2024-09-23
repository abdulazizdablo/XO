<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Section;
use Faker\Factory as Faker;



class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        Category::create([
                'section_id' => 1,
                'name' =>['en' => 'SHIRTS','ar' => "قمصان",],
                'photo_url' => $faker->imageUrl,
                'thumbnail' => $faker->imageUrl,
                'valid' => $faker->boolean(),
          ]);
        Category::create([
            'section_id' => 1,
            'name' =>['en' => 'T-SHIRTS','ar' => "كنزات",],
            'photo_url' => $faker->imageUrl,
            'thumbnail' => $faker->imageUrl,
            'valid' => $faker->boolean(),
          ]);
        Category::create([
            'section_id' => 1,
            'name' =>['en' => 'PANTS','ar' => "بناطيل",],
            'photo_url' => $faker->imageUrl,
            'thumbnail' => $faker->imageUrl,
            'valid' => $faker->boolean(),
          ]);
        Category::create([
            'section_id' => 1,
            'name' =>['en' => 'SHORTS','ar' => "شورتات",],
            'photo_url' => $faker->imageUrl,
            'thumbnail' => $faker->imageUrl,
            'valid' => $faker->boolean(),
         ]);
         Category::create([
            'section_id' => 2,
            'name' =>['en' => 'SHIRTS','ar' => "قمصان",],
            'photo_url' => $faker->imageUrl,
            'thumbnail' => $faker->imageUrl,
            'valid' => $faker->boolean(),
      ]);
    Category::create([
        'section_id' => 2,
        'name' =>['en' => 'T-SHIRTS','ar' => "كنزات",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
      ]);
    Category::create([
        'section_id' => 2,
        'name' =>['en' => 'PANTS','ar' => "بناطيل",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
      ]);
    Category::create([
        'section_id' => 2,
        'name' =>['en' => 'SHORTS','ar' => "شورتات",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
     ]);
     Category::create([
        'section_id' => 3,
        'name' =>['en' => 'SHIRTS','ar' => "قمصان",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
  ]);
    Category::create([
        'section_id' => 3,
        'name' =>['en' => 'T-SHIRTS','ar' => "كنزات",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
    ]);
    Category::create([
        'section_id' => 3,
        'name' =>['en' => 'PANTS','ar' => "بناطيل",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
    ]);
    Category::create([
        'section_id' => 3,
        'name' =>['en' => 'SHORTS','ar' => "شورتات",],
        'photo_url' => $faker->imageUrl,
        'thumbnail' => $faker->imageUrl,
        'valid' => $faker->boolean(),
    ]);
    Category::create([
      'section_id' => 4,
      'name' =>['en' => 'HOME STYLE','ar' => "هوم ستايل",],
      'photo_url' => $faker->imageUrl,
      'thumbnail' => $faker->imageUrl,
      'valid' => $faker->boolean(),
  ]);
  Category::create([
      'section_id' => 4,
      'name' =>['en' => 'Pillows','ar' => "المخدات",],
      'photo_url' => $faker->imageUrl,
      'thumbnail' => $faker->imageUrl,
      'valid' => $faker->boolean(),
  ]);
    }
}
