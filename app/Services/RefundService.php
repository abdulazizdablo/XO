<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Collection;
use App\Models\SubOrder;
use App\Exceptions\OutOfStockException;
 use Exception;

class RefundService
{



public function calculateRefundPrice($order_items)
{
    $total_refund =  0;

    $order_items->each(function($item) use (&$total_refund) {
        $total_refund += $item->price;
    });

    return $total_refund;
}


 public function calculateStock($order_items)
    {
        $order_items->each(function ($item) {


            // check if the order that was deilverd from inventory have the product quantity of the same exhcange product
            $inventory_id = $item->order()->first()->inventory()->first()->id;

           $order_id = $item->order_id;
          /*  if (!$item->product_variation()->first()->stock_levels()->where('inventory_id',  $inventory_id)->first()) {

                SubOrder::create([

                    //'order_id' => $order_id ,
                   // 'product_variation_id' => $item->product_variation_id,
                   // 'from_inventory' =>   4,
                   // 'to_inventory' =>  $inventory_id
                   
                   'order_item_id' => $item->id,
                   
                   'packaging_id' => 1



                ]);
            } else*/
            
            
            
            
            if ( $item->order()->first()->inventory()->first()->id !== $item->product_variation()->first()->stock_levels()->where('product_variation_id', $item->product_variation_id)->first());


            $order_item_in_inventory_stock = $item->order()->first()->inventory()->first()->stock_levels()->where('product_variation_id', $item->product_variation_id)->first();


/*if($order_item_in_inventory_stock->current_stock_level < $item->quantity){
    
    
    throw new Exception('There is not enough quantity',400);
    
    
}

else{*/
    
    
 
            $order_item_in_inventory_stock->current_stock_level += $item->quantity;



            $order_item_in_inventory_stock->save();   
//}


        });





        /* if ($item->suborder()->exists()) {

                $order_item_in_inventory = $item->suborder()->inventory()->stock_levels()->where('product_varation_id', $item->product_variation_id)->first();


                $order_item_in_inventory->currnet_stock_level += $item->quantity;



                $order_item_in_inventory->save();
            } else {


                $order_item_in_inventory = $item->order()->inventory()->stock_levels()->where('product_varation_id', $item->product_variation_id)->first();


                $order_item_in_inventory->currnet_stock_level += $item->quantity;
                $order_item_in_inventory->save();
            }
        */
    }



    public function checkExchangeProducts($order_items){
        // check the order items are met the conditions
        $refund_period = now()->subDays(15);
    
        $order_items->filter(function ($item) use ($refund_period) {
            return $item->order->reciving_date  > $refund_period  && $item->product_variation()->category()->where('name->en', '')->orWhere('name->ar', '')->first();
        });
    
        $order_items_offers = [];

// Group items by group_id and promotion_name
$groupedItems = $order_items->groupBy(['group_id', 'promotion_name']);

foreach ($groupedItems as $group) {
    // Identify the promotion type for the current group
    $promotionType = $group->first()->promotion_name;

    // Filter items based on the promotion type
    $promotionItems = $group->filter(function ($item) use ($promotionType) {
        return $item->promotion_name == $promotionType;
    });

    // Apply different logic based on the promotion type
    switch ($promotionType) {
        case 'bogo':
            // Identify pairs and add them to $order_items_offers
            for ($i =  0; $i < $promotionItems->count(); $i +=  2) {
                if ($promotionItems->has($i +  1)) {
                    $order_items_offers[] = $promotionItems[$i]->id;
                    $order_items_offers[] = $promotionItems[$i +  1]->id;
                }
            }
            break;
        case 'bogh':
            // Identify trios and add them to $order_items_offers
            for ($i =  0; $i < $promotionItems->count(); $i +=  2) {
                if ($promotionItems->has($i +  1)) {
                    $order_items_offers[] = $promotionItems[$i]->id;
                    $order_items_offers[] = $promotionItems[$i +  1]->id;
             
                }
            }
            break;
        case 'btgo':
            // Identify trios and add them to $order_items_offers
            for ($i =  0; $i < $promotionItems->count(); $i +=  3) {
                if ($promotionItems->has($i +  1) && $promotionItems->has($i +  2)) {
                    $order_items_offers[] = $promotionItems[$i]->id;
                    $order_items_offers[] = $promotionItems[$i +  1]->id;
                    $order_items_offers[] = $promotionItems[$i +  2]->id;
                }
            }
            break;
        case 'flash_sales':
            // Identify items and add them to $order_items_offers
            for ($i =  0; $i < $promotionItems->count(); $i++) {
                $order_items_offers[] = $promotionItems[$i]->id;
            }
            break;
        // Add cases for 'discount' and any other promotion types as needed
    }
}


// Remove the items that form pairs from the original collection
$order_items = $order_items->filter(function ($item) use ($order_items_offers) {
    return !in_array($item->id, $order_items_offers);
});

return $order_items;
    }
    
}
