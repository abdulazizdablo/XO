<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variation>
 */
class VariationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = $this->faker->randomElement(['size', 'color']);
        if ($type == 'size') {
            $property = [
                'ar'=> 'قياس',
                'en' => 'Size',
            ];
            $value = $this->faker->randomElement(['S','M','L','XL','XX','1*2*3', '4*6*8', '6*8*10*12', '6*12*18*24', '10*12*14']);
        } elseif ($type == 'color') {
            $property = [
                'ar'=> 'لون',
                'en' => 'Color',
            ];
            $value = [
                'ar'=> $this->faker->colorName(),
                'en' => $this->faker->colorName(),
            ];
        }

        return [
            'type' => Str::lower($property['en']),
            'property' =>  $property,
            'value' => $value,
            'hex_code' => $type == 'color' ? $this->faker->hexColor() : null ,
        ];
    }
}
