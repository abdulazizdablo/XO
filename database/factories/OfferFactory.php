<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // $name = '';
        $type = $this->faker->randomElement(['bogo', 'bogh', 'btgo', 'flash', 'pair']);
        $name = $this->faker->randomElement([[
            "en" => 'But One Get One Free',
            "ar" => 'اشتري واحدة واحصل على الثانية مجانا'
        ], [
            "en" => 'But One Get One Half Price Free',
            "ar" => 'اشتري واحدة واحصل على الثانية بنصف الثمن'
        ], [
            "en" => 'But One Get One Half Price Free',
            "ar" => 'اشتري واحدة واحصل على الثانية بنصف الثمن'
        ], [
            "en" => 'flash',
            "ar" => 'حسم لمدة محدودة'
        ], [
            "en" => 'pair',
            "ar" => 'منتجات أزواج'
        ]]);
        // if ($type == 'bogo') {
        //     $type = 'bogo';
        //     $name = [
        //         "en" => 'But One Get One Free',
        //         "ar" => 'اشتري واحدة واحصل على الثانية مجانا'
        //     ];
        // } elseif ($type == 'bogh') {
        //     $type = 'bogh';
        //     $name = [
        //         "en" => 'But One Get One Half Price Free',
        //         "ar" => 'اشتري واحدة واحصل على الثانية بنصف الثمن'
        //     ];
        // } elseif ($type == 'btgo') {
        //     $type = 'btgo';
        //     $name = [
        //         "en" => 'But Two Get One Free',
        //         "ar" => 'اشتري اثنين واحصل على الثالثة مجانا'
        //     ];
        // }elseif ($type == 'pair') {
        //     $type = 'pair';
        //     $name = [
        //         "en" => 'pair',
        //         "ar" => 'منتجات أزواج'
        //     ];
        // }elseif ($type == 'flash') {
        //     $type = 'flash';
        //     $name = [
        //         "en" => 'flash',
        //         "ar" => 'حسم لمدة محدودة'
        //     ];
        // }

        return [
            'name' => $name,
            'type' => $type,
        ];
    }
}
