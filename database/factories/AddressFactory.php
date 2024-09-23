<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
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
            'city' => $this->faker->city() ,
            'neighborhood' => $this->faker->streetName() ,
            'street' => $this->faker->streetName() ,
            'another_details' => $this->faker->buildingNumber() ,
            'lat_long' => $this->faker->latitude(34.5,35.5).','.$this->faker->longitude(34.5,35.5),
            'phone_number_two' => $this->faker->phoneNumber() ,
        ];
    }
}
