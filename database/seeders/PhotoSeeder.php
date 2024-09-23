<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Photo;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Faker\Factory as Faker;


class PhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        
        $faker = Faker::create();
        $photos = [
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/1?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/2?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/3.5?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/4.5?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/6?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/8?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/9?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/10?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/11?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/12?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/20?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/13?_a=E   ",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/21?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/15?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/14?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/17?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/18?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/22?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/23?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/24?_a=E",
            "https://res.cloudinary.com/dpuuncbke/image/upload/q_auto/f_auto/v1/photo/25?_a=E",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/rw1at0jskkinnfncigwr?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/fngm3mtfpzolfgjdpjix?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/aakv4ritf2goml92ufap?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/venofg7epp3uzn4hblxe?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/luizuesrwzdlxjwnpt9u?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/dxoz32k3xl2lzjdm8ft3?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/ytdmrewrzo152mcpo3b2?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/ypredzstufmy0f3eymb6?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/xumi1gxgdzxwb7ohqrgv?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/mlezhbtbfduuoopjktwf?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/skz2hpcyepjnv2oafoil?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/z2vwkvd7oa5kpsac1rxg?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/xc9ofdyargtjwatzpewk?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/kveuq7vihjd45slwdt0s?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/upb6ok0w6n9kr5yah0ef?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/gyaprmthqwwd4ljfjyws?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/x1oolq8jo84jzpbiasxe?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/fbjjxqskhlzpn1o4uh4d?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/w9rag7p1apr9n1tmnkcq?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/qe3xamqazmagi5vrp7ur?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/s5pammdjvicnj0ifl1gm?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/xpjzkk1hpx04emijjs91?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/inro6d6ua2evbdexndgo?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/pmck0cr5hzgjh1bruxz8?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/djkgtuanxohqsur28a7s?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/fsboi4hmy5qkzisrv3ok?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/minc3vgysnsswyaibryi?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/jnptkp3wvplv0kl97gpg?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/udiyi5rbtejzvdmpyq83?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/czh7ddlwchzix9rbey8e?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/uoxw5fo34yvc5pgkqad9?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/vflclfznyn7jvpap2vji?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/cgjzbkdcjoeo0hivwgh0?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/frizo2amdmcvzhepvvbj?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/h5d0nqqj8ascyt0ldgxv?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/h2enznyska3m3xq77xkb?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/aghm2byanhibtrgnt59m?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/m2hmyhax5immkhksmcih?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/c1cz0og7z9uvih1wshyp?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/l2gvseubgobxbgs77oax?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/rl98khxoj7gzjjiq1prp?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/z8v4igy9cndjfpxyls4q?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/wgwunjyrzpq11grss8z1?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/k2qzkoaj1unk1ef1tmuh?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/zal2tex8kbx2juztxli0?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/wdjvyemm4p6igewefkz0?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/cihhjt3xoahms4sqbwgg?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/pkbu1hbsrdiw7made4ln?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/iby6zwhehmmyzkc7bvrb?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/dhplvd2ti4l9qmfdo3qt?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/g323oioyyy8xisgmi7wo?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/pkhc7lzutcfpigo5cpq4?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/ij7w3vyailjrslrf30rb?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/wdj1cq65mvtqjos25liu?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/qke4snuvywr7j63yndhv?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/vht1ejzdng7emvebx2ei?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/iwqlnnqfkc0nu6xmltsi?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/evpzchklyapjwrik4h8v?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/yfwlankpkcnconoszaqs?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/vnq6xt15gxdipvkubbw8?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/corvbrlh9l67wsn2uh08?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/w8t03foxdpeldhg2ntuc?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/exyl4cjs6q4qtm1slreu?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/wpvp9mos6uizlzdzcyqk?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/smcz75uajljszkoqwpp8?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/rw5vteirfw9u0vy8xnv3?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/nfez1bkvvf8d2qym65v0?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/g4lpoem0xghossvmz2ak?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/hbo5kioxfv4thvu8gfyn?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/cb4j4f1zzqsguobyp0w6?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/i9oaiwlweu0fwx0ktcua?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/z3c5ky7ota2ijdrnmxpx?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/svaroovbpofxadfchg1q?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/zfrsj4l9e2tektzawutd?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/y4uxdejol84gtpaivcaa?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/p1yjebtvqpje7e4t9rkb?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/dnomtzfwrirlyunxolmj?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/uthixnukghsax8ygfzz8?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/tqyt5r03xkjndhhpca94?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/ju2risu4fvrpymxl7geg?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/fcrqmwodc1f7sydpahky?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/het6qwhsaqjfktis0taj?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/rna1koducv8zum4icdem?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/%282%29?_a=BAAAROBs",
            // "https:\/\/res.cloudinary.com\/dpuuncbke\/image\/upload\/q_auto\/f_auto\/v1\/products\/%281%29?_a=BAAAROBs"
        ];
        // for ($j = 1; $j < 82; $j++) {
        //     array_push($photos,
 //  "images/products/($j).webp");
        // }
        $products = Product::with('colors')->get();

        foreach ($products as $product) {
            $colors = $product->colors->pluck('id');
            $product_variations = $product->product_variations->pluck('id');
            Photo::create([
                'product_id' => $product->id,
  
                'color_id' => $faker->randomElement($colors) ?? 1,
  
                'path' =>  $faker->randomElement($photos),
  
                'thumbnail' =>  $faker->randomElement($photos),
  
                'main_photo' => 1
            ]);
            for ($i = 0; $i < 5; $i++) {
                Photo::create([
                    'color_id' => $faker->randomElement($colors) ?? 1,
  
                    'product_id' => $product->id,
  
                    'path' =>  $faker->randomElement($photos),
  
                    'thumbnail' =>  $faker->randomElement($photos),
  
                    'main_photo' => 0
                ]);
            }
        }

        //     for ($i = 0; $i < 500; $i++) {
        //     Photo::create([
        //         'product_id' => Product::inRandomOrder()->first()->id,
  
        //         'color_id' => Color::inRandomOrder()->first()->id,
  
        //         'path' =>  $faker->randomElement($photos),
  
        //         'thumbnail' =>  $faker->randomElement($photos),
  
        //         'main_photo' => $faker->boolean(),
  
        //     ]);}

    }
}
