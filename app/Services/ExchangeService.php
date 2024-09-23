<?php


namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Collection;
use App\Models\SubOrder;
class ExchangeService
{

   

    public function calculateStock(Collection $order_items)
    {
        $order_items->each(function ($item) {


            // check if the order that was deilverd from inventory have the product quantity of the same exhcange product
            $inventory_id = $item->order()->inventory()->id;

$order_id = $item->order()->id;
           /* if (!$item->product_variation()->stock_levels()->where('inventory_id',  $inventory_id)->first()) {

                SubOrder::create([

                    'order_id' => $order_id ,
                    'product_variation_id' => $item->product_variation_id,
                    'from_inventory' =>   ,
                    'to_inventory' =>  $inventory_id



                ]);*/
            //} 
            
            
            if ( $item->order()->inventory()->id == $item->product_variation()->inventory()->stock_levels()->where('product_varation_id', $item->product_variation_id)->first()->inventory_id);


            $order_item_in_inventory_stock = $item->order()->inventory()->stock_levels()->where('product_varation_id', $item->product_variation_id)->first();





            $order_item_in_inventory_stock->currnet_stock_level += $item->quantity;



            $order_item_in_inventory_stock->save();
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



    public function getProductPriceDiff($product_price, $replaced_product_price)
    {
        return $product_price - $replaced_product_price;
    }




    public function checkQuantityEquality($order_item, $replaced_items)
    {


        return $order_item->quantity == $replaced_items->quantity;
    }
	
	public function checkExchangeProducts($order_items){
	//	
	}

}
