<?php

namespace Database\Factories;

use App\Models\ProductVariation;
use App\Models\Color;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $photos=[];
        for ($j = 1; $j < 51; $j++) {
        array_push($photos,"images/products/($j).webp");
        }

        /*                'product_id' => $product->id,
               */
        return [

            'path' =>  $this->faker->randomElement($photos),
            'thumbnail' =>  $this->faker->randomElement($photos),
            'main_photo' => $this->faker->boolean()
        ];
    }
}
