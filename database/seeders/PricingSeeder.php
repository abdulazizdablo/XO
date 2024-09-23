<?php

namespace Database\Seeders;

use App\Models\Pricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;


class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */


    public function run()
    {
        // Pricing::factory(200)->create();
        $faker = Faker::create();
        $products = Product::all();
        foreach ($products as $product) {
                Pricing::create([
                    'product_id' => $product->id,
                    'name' => [
                        'en' => 'Syrian Pound',
                        'ar' => 'ليرة سورية',
                    ],
                    'location' => 'sy',
                    'currency' => 'SYP',
                    'value' => ceil($faker->numberBetween(10000,150000) / 1000) * 1000,
                    'valid' => true,
                ]);
        }
    }
}
