<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'inventory_id' => Inventory::inRandomOrder()->first()->id,
            'account_id' => Account::inRandomOrder()->first()->id,

            'shift_id' => Shift::inRandomOrder()->first()->id ,
            'first_name' => $this->faker->firstName() ,
            'last_name' => $this->faker->lastName() ,
            'email' => $this->faker->email() ,
            'phone' => $this->faker->phoneNumber() ,
            'password' => Hash::make('password') ,
        ];
    }
}
