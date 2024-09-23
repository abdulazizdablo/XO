<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BanHistory>
 */
class BanHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $month1 = $this->faker->numberBetween(1, 12);
        $year1 = $this->faker->numberBetween(2017, 2023);
        $day1 = $this->faker->numberBetween(1, 31);

        $start_date = Carbon::create($year1, $month1, $day1);

        $month2 = $this->faker->numberBetween(1, 12);
        $year2 = $this->faker->numberBetween(2017, 2023);
        $day2 = $this->faker->numberBetween(1, 32);

        $end_date = Carbon::create($year2, $month2, $day2);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'reason' => $this->faker->text(20),
        ];
    }
}
