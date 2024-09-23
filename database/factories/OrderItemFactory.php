<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ProductVariation;
use App\Models\ReturnOrder;
use App\Models\Sku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // $return_id = $this->faker->randomElement([ReturnOrder::inRandomOrder()->first()->id, null]);
        return [
            'return_order_id' => null,
            'order_id' => Order::inRandomOrder()->first()->id,
            'product_variation_id' => ProductVariation::inRandomOrder()->first()->id,
            'quantity' => $this->faker->randomDigit(),
            'price' => $this->faker->randomDigit(),
            'original_price' => $this->faker->randomDigit(),
            'reason' => null,
            // 'reason' => $return_id == null ? null : $this->faker->text(20),
        ];
    }
}
