<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $month = $this->faker->numberBetween(1, 12);
        $year = $this->faker->numberBetween(2017, 2023);
        $day = $this->faker->numberBetween(1, 31);

        $createdAt = Carbon::create($year, $month, $day);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'order_id' => Order::inRandomOrder()->first()->id,
            'type' => "Product specifications do not match",
            'content' => $this->faker->text(20) ,
            'rate' => $this->faker->numberBetween(1,5) ,
            'status' => $this->faker->randomElement(['solved','opened']),
            'created_at' => $createdAt
        ];
    }
}
