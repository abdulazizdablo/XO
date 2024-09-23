<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Group;
use App\Models\Product;
use App\Models\Size;
use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariation>
 */
class ProductVariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'group_id' => $this->faker->randomElement([Group::inRandomOrder()->first()->id, null]),
            'product_id' => Product::inRandomOrder()->first()->id,
            'variation_id' => null,
            'color_id' => Color::inRandomOrder()->first()->id,
            'size_id' => Size::inRandomOrder()->first()->id,
            'sku_code' => $this->faker->randomNumber(6,true),
        ];
    }
}
