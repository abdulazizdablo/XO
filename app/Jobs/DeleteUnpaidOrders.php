<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLevel;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DeleteUnpaidOrders implements ShouldQueue
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
		$orders = Order::where([['created_at', '<', now()->subMinutes(3)],['paid',0],['payment_method','!=','cod']])->get();
		
		// Loop through each shipment and update the date to the next day
		foreach ($orders as $order) {
			$order_id = $order->id;
			$original_order_id = $order->original_order_id;
			
			if($original_order_id != null){
				$original_order_items = OrderItem::where([['order_id', $original_order_id],['status','return']])->get();
				foreach($original_order_items as $original_order_item){
					$original_order_item->update(['status'=>null]);		
				}
			}
			
			$inventory_id = Order::where('id', $order_id)->first()->inventory_id;
		
			$product_variations_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])
				->where('order_id', $order_id)->get();
			
			foreach ($product_variations_ids as $pv) {
				
				$stock = StockLevel::where([['inventory_id', $pv->to_inventory],
											['product_variation_id', $pv->product_variation_id]])->first();
				if(!$stock){
						$stock = StockLevel::create([
						'product_variation_id' => $pv->product_variation_id,
						'inventory_id' => $pv->to_inventory,
						'name' => Str::random(5),
						'min_stock_level' => 3,
						'max_stock_level' => 1000,
						'target_date' => now(),
						'sold_quantity' => 0,
						'status' => 'slow-movement',
						'current_stock_level' => 0
					]);		
				}
					
				$stock->update([
					'on_hold' => $stock->on_hold - $pv->quantity,
					'current_stock_level' => $stock->current_stock_level + $pv->quantity
				]);
			}
				
			// Ensure 'closed' is fillable in your Order model
			$order->forceDelete();
		}
    }
}