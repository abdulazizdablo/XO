<?php

namespace Database\Seeders;

use App\Models\GroupOffer;
use App\Models\Product;
use App\Models\Group;
use App\Models\Offer;
use Faker\Factory as Faker;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $offers_id_array = [];
        for ($i = 0; $i < 30; $i++) {
            $offers_id_array[] = Offer::inRandomOrder()->first()->id;
        }

        foreach ($offers_id_array as $offer_id) {
            $offer = Offer::where('id', $offer_id)->first();
            $group = Group::where('type', 'offer')->inRandomOrder()->first();
            $group->update([
                'type' => 'offer',
                'promotion_type' => $offer->type
            ]);

            GroupOffer::create([
                'group_id' => $group->id,
                'offer_id' => $offer_id,
                'valid' => true,
            ]);
        }
    }
}
