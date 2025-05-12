<?php


namespace App\Services;

use Illuminate\Support\Collection;
class ExchangeService
{

   

    public function calculateStock(Collection $order_items)
    {
        $order_items->each(function ($item) {
            // check if the order that was deilverd from inventory have the product quantity of the same exhcange product
            if ( $item->order()->inventory()->id == $item->product_variation()->inventory()->stock_levels()->where('product_varation_id', $item->product_variation_id)->first()->inventory_id);
            $order_item_in_inventory_stock = $item->order()->inventory()->stock_levels()->where('product_varation_id', $item->product_variation_id)->first();
            $order_item_in_inventory_stock->currnet_stock_level += $item->quantity;
            $order_item_in_inventory_stock->save();
        });
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
