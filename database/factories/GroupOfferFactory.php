<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupOffer;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupOffer>
 */
class GroupOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $offers_id_array = [];
        for ($i = 0; $i < 10; $i++) {
            $offers_id_array[] = Offer::inRandomOrder()->first()->id;
        }

        for ($i = 0; $i < 5; $i++) {
            $product_variations_id_array[] = ProductVariation::inRandomOrder()->first();
        }

        $group = Group::where('type', 'offer')->inRandomOrder()->first();

        foreach ($offers_id_array as $offer_id) {
            $offer = Offer::where('id', $offer_id)->first();
            // $group->promotion_type = $offer->type;
            $group->update([
                'type' => 'offer',
                'promotion_type' => $offer->type
            ]);
            $valid = $this->faker->boolean();
            foreach ($product_variations_id_array as $product_variations_id_array) {
                GroupOffer::create([
                    'group_id' => $group->id,
                    'offer_id' => $offer_id,
                    'valid' => $valid,
                ]);
            }
        }
    }
}
