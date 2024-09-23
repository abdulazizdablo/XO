<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            //
            'city_id' => City::inRandomOrder()->first()->id,
            'name' => $this->faker->randomElement(['الدويلعة', 'مساكن الحرس','الحمرا','المحافظة','القرداحة', 'يبرود ','المنطقة الصناعية ','الرمال الذهبية ','الحواش']),
        ];
    }
}
