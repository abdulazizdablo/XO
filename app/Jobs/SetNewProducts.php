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
use Illuminate\Support\Facades\Log;



class SetNewProducts implements ShouldQueue
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
		$new = Product::where('isNew',1)->get();
		foreach ($new as $n){
			$n->update(['isNew'=>0]);				
		}
		
		$products = Product::whereDate('created_at', '>', now()->subDays(7))->get();
		foreach ($products as $product){
			$product->update(['isNew'=>1]);
		}
	}
      
}
