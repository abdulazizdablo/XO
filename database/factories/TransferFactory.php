<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'from_location_id' => Location::inRandomOrder()->first()->id,
            'to_location_id' => Location::inRandomOrder()->first()->id,
            'deliver_date' => $this->faker->dateTime(),
        ];
    }
}
