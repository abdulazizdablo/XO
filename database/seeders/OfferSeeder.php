<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      
        Offer::create(["name"=>[ "en" => 'But One Get One Free',
        "ar" => 'اشتري واحدة واحصل على الثانية مجانا'],"type"=>"bogo"]);
        Offer::create(["name"=>[ "en" => 'But One Get One Half Price Free',
        "ar" => 'اشتري واحدة واحصل على الثانية بنصف الثمن'],"type"=>"bogh"]);
        Offer::create(["name"=>[ "en" => 'But One Get One Half Price Free',
        "ar" => 'اشتري واحدة واحصل على الثانية بنصف الثمن'],"type"=>"btgo"]);
        Offer::create(["name"=>[ "en" => 'flash',
        "ar" => 'حسم لمدة محدودة'],"type"=>"flash"]);
        Offer::create(["name"=>["en" => 'pair',
        "ar" => 'منتجات أزواج'],"type"=>"pair"]);

    }
}
