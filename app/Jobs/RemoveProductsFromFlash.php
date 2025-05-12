<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Discount;
use App\Models\Group;
use Illuminate\Support\Facades\Log;

class RemoveProductsFromFlash implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        try {
			
			//Discount::where('valid', 0)->update(['valid' => 1]);

            // Get expired discounts
            $expiredDiscountIds = Discount::where('end_date', '<', now())->orWhere('valid',0)->pluck('id')->toArray();

            // Get products associated with expired discounts
            $productsToUpdate = Product::whereHas('discount', function ($query) use ($expiredDiscountIds) {
                $query->whereIn('id', $expiredDiscountIds);
            })->with('product_variations')->get();
            // Update products and product variations
            foreach ($productsToUpdate as $product) {
                $product->update([
                    'group_id' => null,
                    'discount_id' => null,
                    'valid' => 0,
                ]);

                $product->product_variations()->update(['group_id' => null]);
            }
			
            // Update expired discounts and related groups
            Discount::whereIn('id', $expiredDiscountIds)->update(['valid' => 0]);
            Group::whereIn('id', Discount::whereIn('id', $expiredDiscountIds)->pluck('group_id'))->update(['valid' => 0]);
			
        } catch (\Exception $e) {
            Log::error("Error in RemoveProductsFromFlash job: " . $e->getMessage());
            throw $e;
        }
    }
}
