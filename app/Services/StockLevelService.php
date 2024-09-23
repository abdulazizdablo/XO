<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockLevel;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Illuminate\Support\Str;
use App\Models\ProductVariation;

class StockLevelService
{

    public function getAllStockLevels()
    {
        $stock_levels = StockLevel::with([
            'product_variation.product:name',
            'product_variation.product.pricing:product_id,location,value'
        ]);

        if (!$stock_levels) {
            throw new InvalidArgumentException('There Is No Stock Level Available');
        }

        return $stock_levels;
    }

    public function getInventoryProducts($sort_data = [], $filter_data = [], $inventory_id = null)
    {
        $stock_levels = StockLevel::select('id', 'product_variation_id', 'inventory_id', 'current_stock_level', 'status', 'created_at')
            ->with([
                'product_variation:id,color_id,size_id,product_id,sku_code',
                'product_variation',
                'product_variation.color',
                'product_variation.size',
                'product_variation.product:id,item_no,name,available,sub_category_id,group_id',
                'product_variation.product.subCategory',
                'product_variation.product.pricing' => function ($query) use ($filter_data, $sort_data) {
                    $query->when(
                        !empty($sort_data) && $sort_data['sort_key'] == 'price',
                        function ($query) use ($sort_data) {
                            if (($sort_data['sort_value']) === 'desc') {
                                $query->orderBy('value', 'DESC');
                            } elseif (($sort_data['sort_value']) === 'asc') {
                                $query->orderBy('value');
                            };
                        }
                    );
                },
                'product_variation.product.photos' => function ($query) {
                    $query->where('main_photo', '1');
                }
            ]);
		if(!is_null($inventory_id)){
			$stock_levels->where('inventory_id', $inventory_id);
		}


        if (!empty($filter_data)) {
            $stock_levels = $this->applyFilters($stock_levels, $filter_data);
			
        }
		
		
			/*    if (!empty($sort_data)) {
            $stock_levels = $this->applySort($stock_levels, $sort_data);
					return $stock_levels->get();

        }*/
		
        // if (!empty($key)) {
        //     // return 'hi';
        //     $stock_levels = $stock_levels->whereHas('product_variation.product', function ($query) use ($key) {
        //         $query->where('item_no', 'like', '%' . $key . '%')
        //         ->orwhere('name->ar', 'like', '%' . $key . '%')
        //         ->orwhere('name->en', 'like', '%' . $key . '%');
        //     })->orWhereHas('product_variation', function ($query) use ($key) {
        //         $query->where('sku_code', 'like', '%' . $key . '%');
        //     });
        // }

        // if (!empty($sort_data)) {
        //     $products = $this->applySort($products, $sort_data);
        // }

        return $stock_levels->paginate(12);
    }

    protected function filterBySearch($query, $filter_data)
    {
        // return $query->whereLike('shipment_name', $filter_data['search']);
        $search = $filter_data['search'];
        // dd($search);
        return $query->whereHas('product_variation.product', function ($query) use ($search) {
            $query->where('item_no', 'like', '%' . $search . '%')
            ->orwhere('name->ar', 'like', '%' . $search . '%')
            ->orwhere('name->en', 'like', '%' . $search . '%');
        })->orWhereHas('product_variation', function ($query) use ($search) {
            $query->where('sku_code', 'like', '%' . $search . '%');
        });
    }
        
        
    

    public function getStockLevel(int $stock_level_id)
    {
        try {
            $stock_level = StockLevel::findOrFail($stock_level_id);

 

        return $stock_level;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function createStockLevel(array $data, int $sku_id, int $location_id): StockLevel
    {
        $stock_level = StockLevel::create([
            'sku_id' => $sku_id,
            'location_id' => $location_id,
            'name' => $data['name'],
            'min_stock_level' => $data['min_stock_level'],
            'max_stock_level' => $data['max_stock_level'],
            'safety_stock_level' => $data['safety_stock_level'],
            'target_date' => $data['target_date'],
            'sold_quantity' => $data['sold_quantity'],
            'status' => $data['status'],
        ]);

        if (!$stock_level) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $stock_level;
    }

    public function updateStockLevel($data, int $stock_level_id)
    {
        $stock_level = StockLevel::findOrFail($stock_level_id);

      
        $stock_level->update($data);

        if (!$stock_level) {
            throw new Exception('Something Wrong Happend');
        }

        return $stock_level;
    }

    public function delete(int $stock_level_id): void
    {
        $stock_level = StockLevel::findOrFail($stock_level_id);

        $stock_level->delete();
    }

    public function forceDelete(int $stock_level_id): void
    {
        $stock_level = StockLevel::findOrFail($stock_level_id);

        $stock_level->forceDelete();
    }

    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);

            if (method_exists($this, $method) && isset($value) && $value != null){
                $query = $this->{$method}($query, $filters);
            }
        }
        return $query;
    }



    // $filter = $request->only(['date', 'stock, 'status ,'price_min', 'price_max']);

    protected function filterByPrice($query, $filter_data)
    {
        $price_min = $filter_data['price_min'] ?? 0;
        $price_max = $filter_data['price_max'] ?? 10000000;
        return $query->whereHas('product_variation.product.pricing', function ($query) use ($price_min, $price_max) {
            return $query->whereBetween('value', [$price_min, $price_max]);
        });
    }

    protected function filterByStock($query, $filter_data)
    {
        $stock_min = $filter_data['stock_min'] ?? 10;
        $stock_max = $filter_data['stock_max'] ?? 100000;
        return $query->whereBetween('current_stock_level', [$stock_min, $stock_max])->orderBy('current_stock_level','desc');
    }

    protected function filterByDate($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? now();
        $date_max = $filter_data['date_max'] ?? now()->addYears(10)->timestamp;
        return $query->whereBetween('created_at', [$date_min, $date_max]);
    }

    protected function filterByStatus($query, $filter_data)
    {

        // in_array($design_id, $list_desings_ids)
        if(in_array('all',$filter_data['status'])) {
            return $query;
        }
        else{
            return $query->whereIn('status', $filter_data['status']);
        }
        
      
    }

    protected function applySort($query, $sort_data)
    {
        $column_name = Str::before($sort_data['sort_key'], '_');
        $method = 'sortBy' . Str::studly($column_name);

        if (method_exists($this, $method) && isset($sort_data['sort_value'])) {
            $query = $this->{$method}($query, $sort_data['sort_value']);
        }
		var_dump($query);

        return $query;
    }

    protected function sortByPrice($query, $value)
    {
        $query->orderBy(function ($query) {
            $query->orderBy('value', 'asc');
        });
        return $query;
    }
}
