<?php

namespace App\Http\Resources;
use App\Models\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductVariationCollection extends ResourceCollection
{
    protected $notified_products_ids;
    public function __construct($resource)
    {
        parent::__construct($resource);

        // $this->resource = $this->collect($resource) ? $resource : null;
      //  $this->notified_products_ids = $notified_products_ids;
    }


    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'product_variations' => $this->collection->transform(function ($item) {
                $quantity = 0;
                $status = 'Out of Stock';
                $is_notified = $this->notified_products_ids->contains($item->id);
                $max_quantity_per_order = 0;
                $stock_levels = $item->stock_levels;
                $has_stock_levels = collect($stock_levels)->isNotEmpty();
                if ($has_stock_levels) {
                    $quantity = $stock_levels->sum('current_stock_level');
                    // $status = 'Out of Stock';
                }

                if ($has_stock_levels  && $quantity == 0) {
                    $status = 'Out of Stock';
                } elseif ($has_stock_levels && $quantity < 4) {
                    $status = 'Last few Items';
                   // $max_quantity_per_order =1;
                } elseif ($has_stock_levels && $quantity >= 4) {
                    $status = 'In Stock';
                   // $max_quantity_per_order = ceil($quantity * 2/100);
                }

                return [
                    'product_variation_id' => $item->id,
                    'quantity' => $quantity,
                    'status' => $status,
                   // 'max_quantity_per_order' =>10 ,
                    'sku_code' => $item->sku_code,
                    // 'locations' => $item->stock_levels->map(function ($item){
                    //     return $item->location->getFields(['id','name']);
                    // }),
                    'notify' => $is_notified,
                    'size' => [
                        'value' => $item->size->value,
                    ],

                ];
            }),
        ];
        // return parent::toArray($request);
    }
}
