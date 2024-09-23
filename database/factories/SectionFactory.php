<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = [
            'en' => 'product 1',
            'ar' => "منتج 1",
        ];
        return [
            'name' => $name,
            'photo_url' => $this->faker->imageUrl,
            'thumbnail' => $this->faker->imageUrl,
        ];
    }
}
