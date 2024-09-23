<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Group;
use App\Models\GroupDiscount;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class GroupDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $discounts_id_array = [];
        for ($i = 0; $i < 5; $i++) {
            $discounts_id_array[] = Discount::inRandomOrder()->first()->id;
        }

        foreach ($discounts_id_array as $discount_id) {
            $group_id = Group::inRandomOrder()->first()->id;

            GroupDiscount::create([
                'group_id' => $group_id,
                'discount_id' => $discount_id,
                'valid' => $faker->boolean(),
            ]);
        }
    }
    // }
}
