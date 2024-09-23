<?php

namespace Database\Factories;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransferItem>
 */
class TransferItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'transfer_id' => Transfer::inRandomOrder()->first()->id,
            'quantity' => $this->faker->randomDigit(),
        ];
    }
}
