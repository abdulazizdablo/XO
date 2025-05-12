<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\CargoShipment;
use App\Models\ProductVariation;
use App\Models\StockLevel;

//use App\Exceptions\OutOfStockException;

class CargoShipmentPVService
{


    public function calculateNewStock(CargoShipment $cargo_shipment, $cargo_shipment_pv, $from_inventory = null, $to_inventory = null) //si
    {
        $from_inventory = $cargo_shipment->from_inventory;
        $products_not_found = [];
        $out_of_stock_products = [];
        $low_stock_products = [];
        $quantity_not_enough = [];
        $products_has_issues = [];
        $iterator = 0;
        $product_variations = ProductVariation::findOrFail(array_column($cargo_shipment_pv, 'product_variation_id'));
        $stockLevels = collect([]);
        foreach ($product_variations as $iterator => $product_variation) {
            $stockLevel = $product_variation->stock_levels()->where('inventory_id', $from_inventory)->first();
            if ($stockLevel) {
                $stockLevels[$iterator] = $stockLevel;
            }
        }
        $productVariationIds = array_column($cargo_shipment_pv, 'product_variation_id');
        $inventory_stock_levels = $stockLevels->filter(function ($item) use ($productVariationIds) {
            return in_array($item['product_variation_id'], $productVariationIds);
        });

        if ($inventory_stock_levels->isEmpty()) {
            $products_has_issues['This Product is not found'] = ProductVariation::FindOrFail($productVariationIds)->pluck('sku_code');
        }

        foreach ($inventory_stock_levels as $iterator => $inventory_stock_level) {

            if (!$inventory_stock_level) {
                $products_not_found[$iterator] = $product_variations[$iterator]->sku_code;
            } else if ($inventory_stock_level) {
                if ($inventory_stock_level->status == 'out_of_stock' || $inventory_stock_level->current_stock_level == 0) {
                    $out_of_stock_products[$iterator] = $product_variations[$iterator]->sku_code;
                } else if ($inventory_stock_level->status == 'low-inventory' || $inventory_stock_level->current_stock_level < 10) {
                    $low_stock_products[$iterator] = $product_variations[$iterator]->sku_code;
                }
                if ($inventory_stock_level->current_stock_level < $cargo_shipment_pv[$iterator]['quantity'] && !($inventory_stock_level->status == 'low-inventory' || $inventory_stock_level->current_stock_level < 10) && !($inventory_stock_level->status == 'out_of_stock' || $inventory_stock_level->current_stock_level == 0)) {
                    $quantity_not_enough[$iterator] = $product_variations[$iterator]->sku_code;
                }
                $iterator++;
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

            if (!empty($quantity_not_enough)) {
                $products_has_issues['This Product has not enough stock'] = array_values($quantity_not_enough);
            }
        }

        if (!empty($products_has_issues)) {

            throw new OutOfStockException($products_has_issues);
        }

        foreach ($cargo_shipment_pv as $index => $item) {
            $stock_level_from = StockLevel::where('product_variation_id', $item['product_variation_id'])->where('inventory_id', $from_inventory)->first();

            if (!$stock_level_from) {

                $products_has_issues['This Product is not found'] =  (array)ProductVariation::findOrFail($item['product_variation_id'])->sku_code;
                throw new OutOfStockException($products_has_issues);
            }

            $newFromStockLevel = $stock_level_from->current_stock_level - $item['quantity'];
            $stock_level_from->current_stock_level = $newFromStockLevel;
            $stock_level_from->save();

            if (!$stockLevels) {

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
            } else {

                if ($item instanceof \Illuminate\Database\Eloquent\Model) {
                    if (isset($cargo_shipment_pv[$index]['quantity'])) {
                    }
                }
            }
        }
    }
}
