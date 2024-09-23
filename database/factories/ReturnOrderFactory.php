<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnOrder>
 */
class ReturnOrderFactory extends Factory
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
            'employee_id' => Employee::inRandomOrder()->first()->id,
            'total_refund' => $this->faker->randomDigit() ,
            'quantity' => $this->faker->randomDigit() ,
            'city' => $this->faker->city() ,
        ];
    }
}
