<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => [
                'ar'=> $this->faker->name(),
                'en' => $this->faker->name(),
            ],
            'type' => $this->faker->name(),
            'image_url' => $this->faker->imageUrl() ,
            'valid' => $this->faker->boolean(),
        ];
    }
}
