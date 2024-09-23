<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Color>
 */
class ColorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker_ar = Faker::create('ar_SA');
        $faker_en = Faker::create('en_EN');

        $name = [
            'ar' => $faker_ar->colorName(),
            'en' => $faker_en->colorName(),
        ];
        static   $id=11;
        return [
            'name' => $name,
            'hex_code' => $this->faker->hexColor(),
            'sku_code' => $this->faker->text(10)
        ];
    }
}
