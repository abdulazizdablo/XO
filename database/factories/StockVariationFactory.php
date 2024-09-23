<?php

namespace Database\Factories;

use App\Models\ProductVariation;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockVariation>
 */
class StockVariationFactory extends Factory
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
            'stock_movement_id' => StockMovement::inRandomOrder()->first()->id,
        ];
    }
}
