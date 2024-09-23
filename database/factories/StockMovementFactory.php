<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startTime = $this->faker->dateTimeBetween('now', '+1 week');
        $endTime = $this->faker->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s').' +10 hours');
        $expected = $this->faker->numberBetween(50, 300);
        $recived = $this->faker->randomElement([ $expected, $expected - $this->faker->numberBetween(10, 50)]);
        $nameFormat = '########';

        $month = $this->faker->numberBetween(1, 12);
        $year = $this->faker->numberBetween(2017, 2023);
        $day = $this->faker->numberBetween(1, 31);

        $createdAt = Carbon::create($year, $month, $day);

        $month1 = $this->faker->numberBetween(1, 12);
        $year1 = $this->faker->numberBetween(2017, 2023);
        $day1 = $this->faker->numberBetween(1, 31);

        $updatedAt = Carbon::create($year1, $month1, $day1);

        return [
            // 'product_variation_id' => ProductVariation::inRandomOrder()->first()->id,
            'from_inventory_id' => Inventory::inRandomOrder()->first()->id,
            'to_inventory_id' => Inventory::inRandomOrder()->first()->id,
            'num_of_packages' => $this->faker->numberBetween(10, 20),
            'shipment_name' => '#TW-'.$this->faker->numerify($nameFormat),
            'status' => $this->faker->randomElement(['closed', 'open']),
            'delivery_date' => $startTime ,
            'shipped_date' => $startTime ,
            'received_date' => $endTime ,
            'expected' => $expected,
            'received' => $recived ,
          //  'created_at' => $createdAt,
            //'updated_at' => $updatedAt,
        ];
    }
}
