<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubOrder>
 */
class SubOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $package_id = $this->faker->randomElement([Package::inRandomOrder()->first()->id, null]);

        return [
            'packaging_id' => $package_id == null ? null : $package_id,
            'order_item_id' => Order::inRandomOrder()->first()->id,
        ];
    }
}
