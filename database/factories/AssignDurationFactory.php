<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AssignDurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {


        return [
            'account_id' => $this->faker->randomNumber(1, Account::count()),
            'employee_id' => $this->faker->randomNumber(1, Employee::count()),
            'assign_to' => $this->faker->randomElement([null, Carbon::create(2023, 4, 5, 12, 22, 32)]),
            'assign_from' => now()
        ];
    }
}
