<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
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

        $last_recharge = Carbon::create($year, $month, $day);

        $password = Crypt::encryptString("password");
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'name' => $this->faker->name() ,
            'code' => $this->faker->unique()->text(8) ,
            'type' => $this->faker->randomElement(['coupon', 'gift']),
            // 'password' => "password",
            'password' =>  $password,
            'valid' => $this->faker->boolean() ,
            'used_redemption' => $this->faker->randomDigit() ,
            'max_redemption' => $this->faker->randomDigit() ,
            'amount_off' => Crypt::encryptString($this->faker->randomElement([15000,200000,2500000,5000000,7500000,700000])) ,
            'percentage' => $this->faker->randomElement([15,20,25,30,50,70]),
            'expired_at' => $this->faker->dateTime() ,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'last_recharge' => $last_recharge
        ];
    }
}
