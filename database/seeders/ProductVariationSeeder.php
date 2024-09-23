<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Discount;
use App\Models\Photo;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Promotion;
use App\Models\Size;
use App\Models\Variation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductVariationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductVariation::factory(100)->create();
    }
}
