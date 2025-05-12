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

class DeactivateCoupon implements ShouldQueue
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
		$coupons = Coupon::where([
			['expired_at', '<', now()],
			['type', 'coupon'],
			['valid', 1],
		])
			->orWhere(function ($query) {
				$query->whereColumn('max_redemption', 'used_redemption')
					->where('type', 'coupon')
					->where('valid', 1);
			})
			->get();
		// Loop through each shipment and update the date to the next day
		foreach ($coupons as $coupon) {
			$coupon->update(['valid'=>0]);
		}
	}
}