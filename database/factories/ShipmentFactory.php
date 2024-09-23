<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'order_id' => Order::inRandomOrder()->first()->id ,
            'city' => $this->faker->city() ,
            'type' => $this->faker->randomElement(['fast_shipping', 'flexible']),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'street' => $this->faker->streetAddress() ,
            'neighborhood' => $this->faker->streetName() ,
            'first_name' => $this->faker->firstName() ,
            'last_name' => $this->faker->lastName() ,
            'lat' => $this->faker->latitude() ,
            'long' => $this->faker->longitude() ,
            'receiver_first_name' =>$this->faker->firstName() ,
            'receiver_last_name' =>$this->faker->lastName() ,
            'receiver_phone' =>$this->faker->phoneNumber() ,
            'receiver_phone2' =>$this->faker->randomElement([null, $this->faker->phoneNumber()]) ,
            'additional_details' => $this->faker->text() ,
            'is_delivered' => $this->faker->boolean() ,
        ];
    }
}
