<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Group;
use App\Models\SubCategory;
use App\Traits\FakerCloth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/*
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    // use FakerCloth;
    /*
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $month = $this->faker->numberBetween(1, 12);
        $year = $this->faker->numberBetween(2017, 2023);
        $day = $this->faker->numberBetween(1, 31);

        $createdAt = Carbon::create($year, $month, $day);

        $month1 = $this->faker->numberBetween(1, 12);
        $year1 = $this->faker->numberBetween(2017, 2023);
        $day1 = $this->faker->numberBetween(1, 31);

        $updatedAt = Carbon::create($year1, $month1, $day1);

        return [
            'group_id' => Group::inRandomOrder()->first()->id,
            'discount_id' => Discount::inRandomOrder()->first()->id,
            'sub_category_id' => SubCategory::inRandomOrder()->first()->id,
            'item_no' => $this->faker->randomNumber(6),
            'available' => $this->faker->boolean(),
            'name' => [
                "en" => $this->faker->randomElement(['Contrasting Print Shirt', 'T-Shirt', 'Short', 'Jeans', 'Dirty Jeans', 'Polo Shirt', 'Dress', 'Skirt', 'Sweater', 'Shirt']),
                "ar" => $this->faker->randomElement(['بلوزة', 'بنطال', 'فستان', 'تنورة', 'بلوزة بولو', 'هودي', 'بنطال جينز', 'شورت', 'هودي', 'قميص']),
            ],
            'description' => [
                "en" => $this->faker->randomElement(['Slim Fit Jeans Blue', 'Washed Regular Fit Jeans Light Grey Denim', 'Casual Jeans Blue', 'Puff Sleeves Printed Dress Prints', 'Floral Print Wrap Dress Multicolour', 'Frill Sleeve Dress With Side Slit Pink', 'Logo Crew Neck T-Shirt', 'Unisex Batwing Logo T-Shirt', 'Gathered Detail Blouse Pink', 'Sequin Midi Skirt Silver', '3 Stripe Essential Shorts', 'Solid Pattern Skinny Fit Pants Midnight Blue']),
                "ar" => $this->faker->randomElement(['منتج ذو جودة عالية', 'بلوزة مخططة', 'بنطال بعدة جيوب', 'فستان مزهر', 'شورت كتان ذو جودة عالية', 'بلوزة مخططة بولو', 'بنطال بتصيم سادة وقصة سكيني أزرق', 'شيرت بشعار الماركة برقبة مستديرة', 'بنطال بتصيم سادة وقصة سكيني أزرق']),
            ],
            'material' => [
                "en" => $this->faker->randomElement(['Cotton', 'Polyester', 'Cotton Blend', 'Polyester Blend', 'Elastane Blend', 'cotton and linen blended']),
                "ar" => $this->faker->randomElement(['قطن', 'بولي ايستر', 'مزيج الإيلاستين', 'مزيج القطن']),
            ],
            'composition' => [
                "en" => $this->faker->randomElement(['Polyester', '100% Cotton', 'Polyester Blend']),
                "ar" => $this->faker->randomElement(['قطن', 'بولي ايستر', 'fab3',]),
            ],
            'care_instructions' => [
                "en" => $this->faker->randomElement(['Machine Wash', 'Dry Clean', 'fab3',]),
                "ar" => $this->faker->randomElement(['غسيل في الغسالة', 'تنظيف جاف', 'fab3',]),
            ],
            'fit' => [
                "en" => $this->faker->randomElement(['fit', 'slim-fit', 'over-size']),
                "ar" => $this->faker->randomElement(['فيت', 'سليم فيت', 'اوفر سايز'])
            ],
            'style' => [
                "en" => $this->faker->randomElement(['sty1', 'sty2', 'sty3']),
                "ar" => $this->faker->randomElement(['ستايل1', 'ستايل2', 'ستايل3']),
            ],
            'season' => [
                "en" => $this->faker->randomElement(['winter', 'summer', 'spring', 'autmun']),
                "ar" => $this->faker->randomElement(['خريف', 'صيف', 'ربيع', 'شتاء']),
            ],
            // 'created_at' => $createdAt,
            // 'updated_at' => $updatedAt,
        ];
    }
}
