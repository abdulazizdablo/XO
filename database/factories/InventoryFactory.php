<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $country = $this->faker->randomElement(['DW', 'LW', 'AW']);
        $warehouse_number = $this->faker->randomDigit();

        return [
            'name' => $this->faker->name() ,
            'city' => $this->faker->city() ,
            'code' => $country.$warehouse_number ,
        ];
    }
}
