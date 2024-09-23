<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Shipment;
use Carbon\Carbon;

class PushUndeliveredOrdersJob implements ShouldQueue
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

		$shipments = Shipment::where([['is_delivered', 0], ['date', '<', now()->subDay()]])->get();
        // Loop through each shipment and update the date to the next day
        foreach ($shipments as $shipment) {
            $shipment->update([
                'date' => now() // Update the date to the next day
            ]);
        }
    }
}