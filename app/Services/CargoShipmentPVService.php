<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\CargoShipment;
use App\Models\CargoShipmentPV;
use App\Models\ProductVariation;
use InvalidArgumentException;
use App\Models\StockLevel;

//use App\Exceptions\OutOfStockException;

class CargoShipmentPVService
{


    public function calculateNewStock(CargoShipment $cargo_shipment, $cargo_shipment_pv, $from_inventory = null,$to_inventory = null)
    {
   

        $from_inventory = $cargo_shipment->from_inventory;

        $products_not_found = [];
        $out_of_stock_products = [];


        $low_stock_products = [];

        $quantity_not_enough = [];



        $products_has_issues = [];



        $iterator = 0;

        $product_variations = ProductVariation::findOrFail(array_column($cargo_shipment_pv, 'product_variation_id'));
	$stockLevels = collect([]); // Array to hold stock levels for each product variation

foreach ($product_variations as $iterator => $product_variation) {
	

    // Fetch the stock level for the current product variation from the specified inventory
    $stockLevel = $product_variation->stock_levels()->where('inventory_id', $from_inventory)->first();
    
    // Check if a stock level was found and add it to the array
    if ($stockLevel) {
        $stockLevels[$iterator] = $stockLevel;
    }
}

// Now, $stockLevels contains the stock levels for each product variation


        // dd(  $cargo_shipment_pv->pluck('product_variation_id'));

		
// Assuming $stockLevels is a collection and you want to filter it based on product_variation_id
$productVariationIds = array_column($cargo_shipment_pv, 'product_variation_id');

$inventory_stock_levels = $stockLevels->filter(function ($item) use ($productVariationIds) {
    return in_array($item['product_variation_id'], $productVariationIds);
});


		
        foreach ($stockLevels as $iterator => $inventory_stock_level) {



           
			
			
			//return $inventory_stock_level;

            if (!$inventory_stock_level) {


                //  throw new OutOfStockException('This Product  with  the SKU ' .$product_variations[$iterator]->sku_code. ' is not found in inventory',null,400);


                $products_not_found[$iterator] = $product_variations[$iterator]->sku_code;
            } else if ($inventory_stock_level) {



                if ($inventory_stock_level->status == 'out_of_stock' || $inventory_stock_level->current_stock_level == 0) {

                    $out_of_stock_products[$iterator] = $product_variations[$iterator]->sku_code;


                    // $products_has_issues['out_of']

                    //  throw new OutOfStockException("Product with " . $product_variations[$iterator]->sku_code . " SKU Code is out of Stock");
                } else if ($inventory_stock_level->status == 'low-inventory' || $inventory_stock_level->current_stock_level < 10) {



                    $low_stock_products[$iterator] = $product_variations[$iterator]->sku_code;


                    // throw new OutOfStockException("Product with " . $product_variations[$iterator]->sku_code . " is low inventory");
                }




                if ($inventory_stock_level->current_stock_level < $cargo_shipment_pv[$iterator]['quantity'] && !($inventory_stock_level->status == 'low-inventory' || $inventory_stock_level->current_stock_level < 10) && !($inventory_stock_level->status == 'out_of_stock' || $inventory_stock_level->current_stock_level == 0)) {



                    $quantity_not_enough[$iterator] = $product_variations[$iterator]->sku_code;


                    // throw new OutOfStockException("Product with " . $product_variations[$iterator]->sku_code . " is low inventory");
                }







                /*else {

                $inventory_stock_level->update(['current_stock_level' => $inventory_stock_level->current_stock_level  - $item->quantity]);
            }
*/





                $iterator++;



                /*  foreach ($cargo_shipment_pv as $quantity => $product_variation_id) {


        
            $product_variation = ProductVariation::findOrFail($product_variation_id)->load('stock_levels');

$stock_level = $product_variation->stock_levels->where('inventory_id',)



           // $product_variation->stock_levels->update(['current_stock_level' => $product_variation->stock_level->current_stock_level - $quantity]);
        }
*/
            }








            if (!empty($products_not_found)) {
                $products_has_issues['This Product is not found'] = array_values($products_not_found);
            }

            if (!empty($out_of_stock_products)) {
                $products_has_issues['This Product is out of stock'] = array_values($out_of_stock_products);
            }

            if (!empty($low_stock_products)) {
                $products_has_issues['This Product is low stock'] = array_values($low_stock_products);
            }

            // Add the third array with the key 'out_of_stock' only if it's not empty
            if (!empty($quantity_not_enough)) {



                $products_has_issues['This Product has not enough stock'] = array_values($quantity_not_enough);
            }
        }



        if (!empty($products_has_issues)) {

          throw new OutOfStockException($products_has_issues);
        }




$cargo_stock_level = []; 



foreach ($cargo_shipment_pv as $index => $item) {

	
	  $stock_level_from = $inventory_stock_levels->where('product_variation_id',$item['product_variation_id'])->where('inventory_id',$from_inventory)->first();
	
		  $stock_level_to = $inventory_stock_levels->where('product_variation_id', $item['product_variation_id'])->where('inventory_id',$to_inventory)->first();
	
	
				     $newFromStockLevel = $stock_level_from->current_stock_level - $item['quantity'];

				    $stock_level_from->current_stock_level = $newFromStockLevel;

	

	if(!$stock_level_to){
	
	
	$stock_level_to = StockLevel::create([
						'product_variation_id' => $item['product_variation_id'],
						'inventory_id' => $to_inventory,
						'name' => 'Shipment',
						'min_stock_level' => 3,
						'max_stock_level' => 1000,
						'target_date' => now(),
						'sold_quantity' => 0,
						'status' => 'slow-movement',
						'current_stock_level' => $item['quantity']
					]);
	
	}
	
	
	else {
	
	   // Update the model's attribute directly and save
          //  $item->update(['current_stock_lev.el' => $newStockLevel]);
		
                $newToStockLevel = $stock_level_to->current_stock_level + $item['quantity'];
            // Update the model's attribute directly and save
          //  $item->update(['current_stock_level' => $newStockLevel]);
			

			$stock_level_to->current_stock_level = $newToStockLevel;

	
			$stock_level_to->save();
	
	
	

			
    // Ensure $item is an instance of an Eloquent model
    if ($item instanceof \Illuminate\Database\Eloquent\Model) {
        // Safely access 'quantity' from $cargo_shipment_pv, checking if the index exists
        if (isset($cargo_shipment_pv[$index]['quantity'])) {
       
        }
    }
	}

         
}





    }
}
