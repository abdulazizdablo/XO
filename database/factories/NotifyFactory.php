<?php

namespace Database\Factories;

use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notify>
 */
class NotifyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'product_variation_id' => ProductVariation::inRandomOrder()->first()->id,
            'notify' => true,
        ];
    }
}
