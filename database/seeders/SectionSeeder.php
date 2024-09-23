<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Section::create([
            "name" => [
                'en' => 'Men',
                'ar' => "رجال",
            ],
            'photo_url' => 'images/section/men.webp',
            'thumbnail' => 'images/section/men.webp',
        ]);
        Section::create([
            "name" => [
                'en' => 'Women',
                'ar' => "نساء"
            ],
            'photo_url' => 'images/section/women.webp',
            'thumbnail' => 'images/section/women.webp',
        ]);
        Section::create([
            "name" => [
                'en' => 'Kids',
                'ar' => "أطفال"
            ],
            'photo_url' => 'images/section/kis.webp',
            'thumbnail' => 'images/section/kis.webp',
        ]);
        Section::create([
            "name" => [
                'en' => 'Home',
                'ar' => "هوم",
            ],
            'photo_url' => 'images/section/home.webp',
            'thumbnail' => 'images/section/home.webp',
        ]);
    }
}
