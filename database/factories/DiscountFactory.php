<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = Faker::create();
        $faker_ar = Faker::create('ar_SA');
        $faker_en = Faker::create('en_EN');
        $name = [
            'ar' => $faker_ar->name(),
            'en' => $faker_en->name(),
        ];

        $type = $this->faker->randomElement(['flash', null]);

        $startTime = $faker->dateTimeBetween('now', '+1 week');
        $endTime = $faker->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s').' +10 hours');
        return [
            'type' => $type,
            'name' => $name,
            'percentage' => $this->faker->randomElement([10,20,30,50,70]),
            'start_date' => $startTime,
            'end_date' => $endTime,
            'valid' => $this->faker->boolean(),
        ];
    }
}
