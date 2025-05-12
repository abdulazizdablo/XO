<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockLevel;
use App\Traits\TranslateFields;
use Illuminate\Database\Eloquent\Builder;
use Intervention\Image\Exception\NotFoundException;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Exception;
use App\Models\SubCategory;

class InventoryService
{
    use TranslateFields;

    public function getAllInventories()
    {
        $inventories = Inventory::select('id', 'name')->get();

        if (!$inventories) {
            throw new InvalidArgumentException('something wrong happened');
        }

        return $inventories;
    }

    public function getProductsStock($filter_data = [], $inventory_id)
    {
        $stocks = Inventory::when($inventory_id, function ($query) use ($inventory_id) {
            $query->where('id', '=', $inventory_id);
        })
            ->select('id')
            ->with([
                'stock_levels:id,inventory_id,product_variation_id,current_stock_level,status,created_at',
                'stock_levels.product_variation:id,product_id,sku_code',
                'stock_levels.product_variation.product:id,name',
            ]);

        if (!empty($filter_data)) {
            $stocks = $this->applyFilters($stocks, $filter_data);
        }

        if (!$stocks) {
            throw new NotFoundException('There Is No Stocks Found');
        }

        $stocks = $stocks->paginate(6);

        return $stocks;
    }

    // public function getInventoryCount($inventory_id, $dateScope,  $from_date, $to_date)
    // {

    //     if ($inventory_id == null) {

    //         $product = Product::all();
    //         $modelName = \App\Models\Product::class;

    //         $total_product = Product::scopeDateRange($product, $modelName, $dateScope, $from_date, $to_date, $inventory_id)->count();

    //         $stock = StockLevel::all();
    //         $modelName1 = \App\Models\StockLevel::class;
    //         $fast_movmint = Product::scopeDateRange($stock, $modelName1, $dateScope, $from_date, $to_date, $inventory_id)->where('status', 'fast-move')->count();

    //         $low_movmint = Product::scopeDateRange($stock, $modelName1, $dateScope, $from_date, $to_date, $inventory_id)->where('status',  'low-move')->count();

    //         $out_of_stock = Product::scopeDateRange($stock, $modelName1, $dateScope, $from_date, $to_date, $inventory_id)->where('status',  'out-of-stock')->count();
    //     } elseif (($inventory_id !== null)) {

    //         $product = Product::with('stocks')->whereHas('stocks', function ($query) use ($inventory_id) {
    //             $query->where('inventory_id', $inventory_id);
    //         })->get();

    //         // return $product;


    //         $modelName = \App\Models\Product::class;
    //         $total_product = Product::scopeDateRange($product, $modelName, $dateScope, $from_date, $to_date, $inventory_id)
    //             ->count();

    //         $stock = StockLevel::all();
    //         $modelName1 = \App\Models\StockLevel::class;
    //         $fast_movmint = Product::scopeDateRange($stock, $modelName1, $dateScope, $from_date, $to_date, $inventory_id)->where('inventory_id', $inventory_id)->where('status', 'fast-move')->count();

    //         $low_movmint = Product::scopeDateRange($stock, $modelName1, $dateScope, $from_date, $to_date, $inventory_id)->where('inventory_id', $inventory_id)->where('status',  'low-move')->count();

    //         $out_of_stock = Product::scopeDateRange($stock, $modelName1, $dateScope, $from_date, $to_date, $inventory_id)->where('inventory_id', $inventory_id)->where('status',  'out-of-stock')->count();
    //     }


    //     $all = [
    //         'total_product' => $total_product,
    //         'fast_movmint' => $fast_movmint,
    //         'low_movmint' => $low_movmint,
    //         'out_of_stock' => $out_of_stock
    //     ];

    //     return response()->json($all, 200);
    // }


    public function getInventoryCount($employee, $inventory_id = null)
    {

        if ($employee->hasRole(Roles::MAIN_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
		

            if ($inventory_id == null) {

                //$total_product = ProductVariation::count();
				$total_product = StockLevel::/*distinct('product_variation_id')->*/count();
                // $stock = StockLevel::all();
                // $modelName1 = \App\Models\StockLevel::class;
                $fast_movement = StockLevel::where('status', 'fast-movement')->count();

                $slow_movement = StockLevel::where('status', 'slow-movement')->count();

                $out_of_stock = StockLevel::whereNotIn('status', ['fast-movement','slow-movement'])->count();
            } elseif (($inventory_id != null)) {
				
				
				
               $total_product = StockLevel::where('inventory_id', $inventory_id)->count();
      																				 
																											 
                // $total_product = StockLevel::where('inventory_id', $inventory_id)->withCount('product_variation');


                $fast_movement = StockLevel::where('inventory_id', $inventory_id)->where('status', 'fast-movement')->count();

                $slow_movement = StockLevel::where('inventory_id', $inventory_id)->where('status', 'slow-movement')->count();
				

                $out_of_stock = StockLevel::where('inventory_id', $inventory_id)->where('status', 'out-of-stock')->count();
            }
        } else {
                       $total_product = StockLevel::where('inventory_id', $employee->inventory_id)->count();


            // $stock = StockLevel::all();
            // $modelName1 = \App\Models\StockLevel::class;
            $fast_movement = StockLevel::where('status', 'fast-movement')->where('inventory_id', $employee->inventory_id)->count();

            $slow_movement = StockLevel::where('status', 'slow-movement')->where('inventory_id', $employee->inventory_id)->count();

            $out_of_stock = StockLevel::where('status', 'out-of-stock')->where('inventory_id', $employee->inventory_id)->count();
        }

        return [
            'total_product' => $total_product,
            'fast_movement' => $fast_movement,
            'slow_movement' => $slow_movement,
            'out_of_stock' => $out_of_stock
        ];
    }

    public function getInventoryProducts($filter_data = [], $inventory_id)
    {
        if (isset($inventory_id)) {
            //$inventory = Inventory::where('id', $inventory_id)->first()->load('stock_levels');
            $inventory = Inventory::findOrFail($inventory_id)->load('stock_levels');
            // return $inventory;
        }
        $stock_levels = $inventory->stock_levels;

        $products = Product::select('id', 'name')->with([
            'product_variations:id,product_id,sku_code,visible',
            'pricing:id,product_id,value,location',
            'stocks' => function ($item) use ($inventory_id) {
                $item->when(isset ($inventory_id), function ($item2) use ($inventory_id) {
                    $item2->where('inventory_id', $inventory_id);
                });
            }
        ])->whereHas('product_variations', function ($q) use ($stock_levels) {
            $q->whereIn('id', $stock_levels->pluck('product_variation_id'));
        })->get();

        if (!empty($filter_data)) {
            $products = $this->applyFilters($products, $filter_data);
        }

        return $products;
    }
	
	
	  public function getInventoryProductVariations($filter_data = [], $inventory_id)
    {
        if (isset($inventory_id)) {
            //$inventory = Inventory::where('id', $inventory_id)->first()->load('stock_levels');
            $inventory = Inventory::findOrFail($inventory_id)->load('stock_levels');
            // return $inventory;
        }
        $stock_levels = $inventory->stock_levels;
		  
		return  $product_variations = ProductVariation::find($stock_levels->pluck('product_variation_id'));
    }

	
	


    public function getInventory(int $inventory_id)
    {
        $inventory = Inventory::findOrFail($inventory_id);

        $inventory_fields = [
            'name',
            'country',
            'city',
            'address',
        ];
        return $inventory->getFields($inventory_fields);
    }

    public function createInventory(array $data, $city)
    {
        //  return $data;

        $inventory = Inventory::create([
            'name' => $data['name'],
            'city' => $city->name,
            'code' => $data['code'],
            'city_id' => $city->id,
        ]);

        if (!$inventory) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $inventory;
    }

    public function updateInventory($data, int $inventory_id)
    {
        $inventory = Inventory::findOrFail($inventory_id);



        if (isset($data['city_en'])) {
            $inventory->update([
                'city' => [
                    'en' => $data['city_en'],
                ],
            ]);
        }
        if (isset($data['city_ar'])) {
            $inventory->update([
                'city' => [
                    'ar' => $data['city_ar'],
                ],
            ]);
        }

        if (isset($data['name'])) {
            $inventory->update([
                'namr' => $data['name']
            ]);
        }

        if (isset($data['code'])) {
            $inventory->update([
                'code' => $data['city_ar']
            ]);
        }

        return $inventory;
    }

	   protected function applySort($query, array $sort_data)
    {
        // return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
		

        return $query->orderBy( $sort_data['sort'], $sort_data['sort_value']);
    }
	
	
    public function show(int $inventory_id): Inventory
    {
        $inventory = Inventory::findOrFail($inventory_id);


        $inventory_fields = [
            'name',
            'country',
            'city',
            'address',
        ];
        return $inventory->getFields($inventory_fields);
    }

    public function delete(int $inventory_id): void
    {
        $inventory = Inventory::findOrFail($inventory_id);



        $inventory->delete();
    }

    public function forceDelete(int $inventory_id): void
    {
        $inventory = Inventory::findOrFail($inventory_id);



        $inventory->forceDelete();
    }

    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);

            if (method_exists($this, $method) && isset($value)) {
                $query = $this->{$method}($query, $filters);
            }
        }
        return $query;
    }



    // $filter = $request->only(['sku_code', 'product_name','price_min', 'price_max']);

    protected function filterByPrice($query, $filter_data,$sort_data = [])
    {
        $price_min = $filter_data['price_min'] ?? 0;
        $price_max = $filter_data['price_max'] ?? 10000000;
		

		
        $query->whereHas('pricing', function ($query) use ($price_min, $price_max) {
            $query->whereBetween('value', [$price_min, $price_max]);
        });

        return $query;
    }

    protected function filterByProduct($query, $filter_data)
    {
        $query->whereHas('stock_levels.product_variation.product', function ($query) use ($filter_data) {
            $query->where('name->' . app()->getLocale(), 'LIKE', '%' . $filter_data['product_name'] . '%');
        });

        return $query;
    }

    protected function filterBySku($query, $filter_data)
    {
        $query->whereHas('stock_levels.product_variation', function ($query) use ($filter_data) {
            $query->where('sku_code', $filter_data['sku_code']);
        })->get();

        return $query;
    }

    public function search($key)
    {
        $query = Product::with('product_variations')
            ->where('item_no', 'LIKE', '%' . $key . '%')
            ->orWhere('name->ar', 'LIKE', '%' . $key . '%')
            ->orWhere('name->en', 'LIKE', '%' . $key . '%')
            ->orWhereHas('product_variations', function ($query) use ($key) {
                $query->where('sku_code', 'LIKE', '%' . $key . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $query;
    }

    public function addToGroup($product_ids, $group_id)
    {
        $group = Product::findOrFail($group_id);



        foreach ($product_ids as $product_id) {
            $product = Product::find($product_id);

            if (!$product) {
                throw new Exception('Product with ID ' . $product_id . ' does not exist');
            }

            $product->group_id = $group_id;
            $product->save();
        }

        return true;
    }

    public function assignToSubCategory($sub_id, $product_ids)
    {
        $sub_category = SubCategory::findOrFail($sub_id);


        foreach ($product_ids as $product_id) {
            $product = Product::find($product_id);

            if (!$product) {
                throw new InvalidArgumentException('Product with ID ' . $product_id . ' does not exist');
            }

            $product->sub_category_id = $sub_id;
            $product->save();
        }

        return true;
    }


}
