<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
/*
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pricing>
 */

class PricingFactory extends Factory
{
    /*
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker_ar = Faker::create('ar_SA');

        return [
            'product_id' => Product::inRandomOrder()->first()->id,
            'name' => [
                'en' => 'Syrian Pound',
                'ar' => 'ليرة سورية',
            ],
            'location' => 'sy',
            'currency' => 'SYP',
            'value' => ceil($this->faker->numberBetween(10000,150000) / 1000) * 1000,
            'valid' => $this->faker->boolean(),
        ];

    }
}
