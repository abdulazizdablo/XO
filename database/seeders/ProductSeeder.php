<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Group;
use App\Models\Product;
use App\Models\Offer;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::factory(20)->create();
        Product::generateRecommendations('sold_together');
        // Discount
        // for ($i = 0; $i < 500; $i++) {
        //     $discount_id = $faker->randomElement([Discount::inRandomOrder()->first()->id, null]);
        //     Product::create(
        //         [
        //             'group_id' => Group::inRandomOrder()->first()->id,
        //             'sub_category_id' => SubCategory::inRandomOrder()->first()->id,
        //             'item_no' => $faker->randomNumber(6),
        //             'available' => $faker->boolean(),
        //             'name' => [
        //                 "en" => $faker->randomElement(['Contrasting Print Shirt', 'T-Shirt', 'Short', 'Jeans', 'Dirty Jeans', 'Polo Shirt', 'Dress', 'Skirt', 'Sweater', 'Shirt']),
        //                 "ar" => $faker->randomElement(['بلوزة', 'بنطال', 'فستان', 'تنورة', 'بلوزة بولو', 'هودي', 'بنطال جينز', 'شورت', 'هودي', 'قميص']),
        //             ],
        //             'description' => [
        //                 "en" => $faker->randomElement(['Slim Fit Jeans Blue', 'Washed Regular Fit Jeans Light Grey Denim', 'Casual Jeans Blue', 'Puff Sleeves Printed Dress Prints', 'Floral Print Wrap Dress Multicolour', 'Frill Sleeve Dress With Side Slit Pink', 'Logo Crew Neck T-Shirt', 'Unisex Batwing Logo T-Shirt', 'Gathered Detail Blouse Pink', 'Sequin Midi Skirt Silver', '3 Stripe Essential Shorts', 'Solid Pattern Skinny Fit Pants Midnight Blue']),
        //                 "ar" => $faker->randomElement(['منتج ذو جودة عالية', 'بلوزة مخططة', 'بنطال بعدة جيوب', 'فستان مزهر', 'شورت كتان ذو جودة عالية', 'بلوزة مخططة بولو', 'بنطال بتصيم سادة وقصة سكيني أزرق', 'شيرت بشعار الماركة برقبة مستديرة', 'بنطال بتصيم سادة وقصة سكيني أزرق']),
        //             ],
        //             'material' => [
        //                 "en" => $faker->randomElement(['Cotton', 'Polyester', 'Cotton Blend', 'Polyester Blend', 'Elastane Blend', 'cotton and linen blended']),
        //                 "ar" => $faker->randomElement(['قطن', 'بولي ايستر', 'مزيج الإيلاستين', 'مزيج القطن']),
        //             ],
        //             'composition' => [
        //                 "en" => $faker->randomElement(['Polyester', '100% Cotton', 'Polyester Blend']),
        //                 "ar" => $faker->randomElement(['قطن', 'بولي ايستر', 'fab3',]),
        //             ],
        //             'care_instructions' => [
        //                 "en" => $faker->randomElement(['Machine Wash', 'Dry Clean', 'fab3',]),
        //                 "ar" => $faker->randomElement(['غسيل في الغسالة', 'تنظيف جاف', 'fab3',]),
        //             ],
        //             'fit' => [
        //                 "en" => $faker->randomElement(['fit', 'slim-fit', 'over-size']),
        //                 "ar" => $faker->randomElement(['فيت', 'سليم فيت', 'اوفر سايز'])
        //             ],
        //             'style' => [
        //                 "en" => $faker->randomElement(['sty1', 'sty2', 'sty3']),
        //                 "ar" => $faker->randomElement(['ستايل1', 'ستايل2', 'ستايل3']),
        //             ],
        //             'season' => [
        //                 "en" => $faker->randomElement(['winter', 'summer', 'spring', 'autmun']),
        //                 "ar" => $faker->randomElement(['خريف', 'صيف', 'ربيع', 'شتاء']),
        //             ],
        //             'promotionable_id' => $discount_id == null ? null : Discount::inRandomOrder()->first()->id,
        //             'promotionable_type' => $discount_id == null ? null : 'App\Models\Discount',
        //         ]
        //     );
        // }

        // Offer
        // for ($j = 0; $j < 500; $j++) {
        //     $promotion_id = $faker->randomElement([Offer::inRandomOrder()->first()->id, null]);
        //     Product::create(
        //         [
        //             'group_id' => Group::inRandomOrder()->first()->id,
        //             'sub_category_id' => SubCategory::inRandomOrder()->first()->id,
        //             'item_no' => $faker->randomNumber(6),
        //             'available' => $faker->boolean(),
        //             'name' => [
        //                 "en" => $faker->randomElement(['Contrasting Print Shirt', 'T-Shirt', 'Short', 'Jeans', 'Dirty Jeans', 'Polo Shirt', 'Dress', 'Skirt', 'Sweater', 'Shirt']),
        //                 "ar" => $faker->randomElement(['بلوزة', 'بنطال', 'فستان', 'تنورة', 'بلوزة بولو', 'هودي', 'بنطال جينز', 'شورت', 'هودي', 'قميص']),
        //             ],
        //             'description' => [
        //                 "en" => $faker->randomElement(['Slim Fit Jeans Blue', 'Washed Regular Fit Jeans Light Grey Denim', 'Casual Jeans Blue', 'Puff Sleeves Printed Dress Prints', 'Floral Print Wrap Dress Multicolour', 'Frill Sleeve Dress With Side Slit Pink', 'Logo Crew Neck T-Shirt', 'Unisex Batwing Logo T-Shirt', 'Gathered Detail Blouse Pink', 'Sequin Midi Skirt Silver', '3 Stripe Essential Shorts', 'Solid Pattern Skinny Fit Pants Midnight Blue']),
        //                 "ar" => $faker->randomElement(['منتج ذو جودة عالية', 'بلوزة مخططة', 'بنطال بعدة جيوب', 'فستان مزهر', 'شورت كتان ذو جودة عالية', 'بلوزة مخططة بولو', 'بنطال بتصيم سادة وقصة سكيني أزرق', 'شيرت بشعار الماركة برقبة مستديرة', 'بنطال بتصيم سادة وقصة سكيني أزرق']),
        //             ],
        //             'material' => [
        //                 "en" => $faker->randomElement(['Cotton', 'Polyester', 'Cotton Blend', 'Polyester Blend', 'Elastane Blend', 'cotton and linen blended']),
        //                 "ar" => $faker->randomElement(['قطن', 'بولي ايستر', 'مزيج الإيلاستين', 'مزيج القطن']),
        //             ],
        //             'composition' => [
        //                 "en" => $faker->randomElement(['Polyester', '100% Cotton', 'Polyester Blend']),
        //                 "ar" => $faker->randomElement(['قطن', 'بولي ايستر', 'fab3',]),
        //             ],
        //             'care_instructions' => [
        //                 "en" => $faker->randomElement(['Machine Wash', 'Dry Clean', 'fab3',]),
        //                 "ar" => $faker->randomElement(['غسيل في الغسالة', 'تنظيف جاف', 'fab3',]),
        //             ],
        //             'fit' => [
        //                 "en" => $faker->randomElement(['fit', 'slim-fit', 'over-size']),
        //                 "ar" => $faker->randomElement(['فيت', 'سليم فيت', 'اوفر سايز'])
        //             ],
        //             'style' => [
        //                 "en" => $faker->randomElement(['sty1', 'sty2', 'sty3']),
        //                 "ar" => $faker->randomElement(['ستايل1', 'ستايل2', 'ستايل3']),
        //             ],
        //             'season' => [
        //                 "en" => $faker->randomElement(['winter', 'summer', 'spring', 'autmun']),
        //                 "ar" => $faker->randomElement(['خريف', 'صيف', 'ربيع', 'شتاء']),
        //             ],
        //             'promotionable_id' => $promotion_id == null ? null : Offer::inRandomOrder()->first()->id,
        //             'promotionable_type' => $promotion_id == null ? null : 'App\Models\Offer',
        //         ]
        //     );
        // }
    }
}
