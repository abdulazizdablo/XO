<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Group;
use App\Models\GroupDiscount;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupDiscount>
 */
class GroupDiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $discounts_id_array = [];
        for ($i = 0; $i < 10; $i++) {
            $discounts_id_array[] = Discount::inRandomOrder()->first()->id;
        }

        for ($i = 0; $i < 5; $i++) {
            $products_id_array[] = Product::inRandomOrder()->first()->id;
        }

        $group_id = Group::discount()->inRandomOrder()->first()->id;

        foreach ($discounts_id_array as $discount_id) {
            $valid = $this->faker->boolean();
            foreach ($products_id_array as $product_id) {
                GroupDiscount::create([
                    'group_id' => $group_id,
                    'discount_id' => $discount_id,
                    'product_id' => $product_id,
                    'valid' => $valid,
                ]);
            }
        }
    }
}
