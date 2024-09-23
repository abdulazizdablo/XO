<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $tag1 = [
            'en' => 'new',
            'ar' => 'جديد',
        ];

        $tag2 = [
            'en' => 'trendy',
            'ar' => 'رائج',
        ];

        $photos = [];
        for ($j = 1; $j < 3; $j++) {
            array_push($photos, "images/offers/offer".($j).".webp");
        }
        $name = [
            'en' => $this->faker->name(),
            'ar' => "منتج 1",
        ];

        return [
            'name' =>$name,
            'type' => $this->faker->randomElement(['offer', 'discount']),
            'promotion_type' => $this->faker->randomElement(['offer', 'discount']),
            'tag' => $this->faker->randomElement($tag1, $tag2),
            'valid' => $this->faker->boolean(),
            'image_path' => $this->faker->randomElement($photos),
            'image_thumbnail' => $this->faker->randomElement($photos),
        ];
    }
}
