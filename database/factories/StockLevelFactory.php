<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\ProductVariation;
use App\Models\Sku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock_level>
 */
class StockLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'product_variation_id' => ProductVariation::inRandomOrder()->first()->id,
            'inventory_id' => Inventory::inRandomOrder()->first()->id,
            'name' => $this->faker->firstName(),
            'min_stock_level' => $this->faker->randomDigit() ,
            'max_stock_level' => $this->faker->randomDigit() ,
            'current_stock_level' => $this->faker->randomDigit() ,
            'target_date' => $this->faker->dateTime() ,
            'sold_quantity' => $this->faker->randomDigit() ,
            'status' => $this->faker->randomElement(['fast-movement', 'slow-movement','low-inventory','out_of_stock']) ,
        ];
    }
}
