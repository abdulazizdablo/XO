<?php

namespace Database\Seeders;

use App\Models\SizeGuide;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeGuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SizeGuide::factory(5)->create();
    }
}
