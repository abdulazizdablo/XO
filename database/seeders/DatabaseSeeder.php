<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CitySeeder::class);
        $this->call(InventorySeeder::class);
        $this->call(ShiftSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(DiscountSeeder::class);
        $this->call(OfferSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BanHistorySeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SubCategorySeeder::class);
        $this->call(ColorSeeder::class);
        $this->call(SizeSeeder::class);
        $this->call(GroupSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CouponSeeder::class);
        // $this->call(VariationSeeder::class);
        $this->call(ProductVariationSeeder::class);
        $this->call(GroupOfferSeeder::class);
        $this->call(GroupDiscountSeeder::class);
        $this->call(PhotoSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(FavouriteSeeder::class);
        $this->call(NotifySeeder::class);
        $this->call(PackageSeeder::class);
        $this->call(OrderSeeder::class);
        //$this->call(FeedbackSeeder::class);
        // $this->call(ReturnOrderSeeder::class);
        $this->call(OrderItemSeeder::class);
        // $this->call(ShipmentSeeder::class);
        $this->call(PricingSeeder::class);
        $this->call(StockLevelSeeder::class);
        // $this->call(SettingSeeder::class);
        $this->call(StockMovementSeeder::class);
        $this->call(StockVariationSeeder::class);
        // $this->call(SizeGuideSeeder::class);
        // $this->call(TransferSeeder::class);
        // $this->call(TransferItemSeeder::class);
        // $this->call(SubOrderSeeder::class);
        // $this->call(CommentSeeder::class);
       // $this->call(RolesAndPermissionsSeeder::class);
        $this->call(RolesSeeder::class);
        
        $this->call(AccountRoleSeeder::class);
        $this->call(AssignDurationSeeder::class);
        $this->call(UserComplaintSeeder::class);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
