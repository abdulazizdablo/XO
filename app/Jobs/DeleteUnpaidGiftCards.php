<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Coupon;
use App\Models\OrderItem;
use App\Models\StockLevel;
use Carbon\Carbon;

class DeleteUnpaidGiftCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		// Get all shipments that are not delivered yet and have a date less than now
		$coupons = Coupon::where([['created_at', '<', now()->subMinutes(15)],['type','gift'],['valid',0]])->get();
		// Loop through each shipment and update the date to the next day
		foreach ($coupons as $coupon) {
			$coupon->forceDelete();
		}
    }
}