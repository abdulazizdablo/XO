<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Carbon\Carbon;

class SetOrderClosedJob implements ShouldQueue
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
		$orders = Order::where('receiving_date', '<', now()->subDays(15))->get();

		// Loop through each shipment and update the date to the next day
		foreach ($orders as $order) {

			// Ensure 'closed' is fillable in your Order model
			$order->update(['closed' => 1]);
		}
    }
}