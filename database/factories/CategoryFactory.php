<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = [
            'en' => $this->faker->name(),
            'ar' => "منتج 1",
        ];
        return [
            'section_id' => Section::inRandomOrder()->first()->id,
            'name' => $name,
            'photo_url' => $this->faker->imageUrl,
            'thumbnail' => $this->faker->imageUrl,
            'valid' => $this->faker->boolean(),
        ];
    }
}
