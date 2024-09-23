<?php

namespace App\Services;

use App\Models\StockMovement;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Str;

class StockMovementService
{

    public function getAllStockMovements($filter_data)
    {
        $stock_movements = StockMovement::query()->with([
            'source_inventory:id,code',
            'destination_inventory:id,code',
        ]);


        if (!empty($filter_data)) {
            $stock_movements = $this->applyFilters($stock_movements, $filter_data);
        }
        // if (isset($filter_data['search'])) {
        //     $search = $filter_data['search'];
        //     $stock_movements->where('shipment_name', 'like', '%' . $search . '%')->get();
        //     };


        $stock_movements = $stock_movements->paginate(6);

        if (!$stock_movements) {
            throw new InvalidArgumentException('There Is No Stock Level Available');
        }

        return $stock_movements;
    }

    public function getCounts()
    {
        $dateScope = request('date_scope');
        $from_date = null;
        $to_date = null ;
        if ($dateScope == null) {
            $dateScope == 'Today';
        }
            $shipment= StockMovement::all();
            $modelName = \App\Models\StockMovement::class;
            $shipments = StockMovement::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->count();
            $closed = StockMovement::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status','closed')->count();
            $open = StockMovement::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status','open')->count();
            $expected =StockMovement::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status','closed')->sum(DB::raw('expected + received'));

        return  [
            'shipments' => $shipments,
            'closed' => $closed,
            'open' => $open,
            'expected' => $expected
        ];

    }


    public function getStockMovement($stock_movement_id): StockMovement
    {
        $stock_movement = StockMovement::find($stock_movement_id)->load([
            'source_inventory:id,name,code',
            'destination_inventory:id,name,code',
        ]);

        // $items = $stock_movement->product_variation

        if (!$stock_movement) {
            throw new InvalidArgumentException('StockMovement not found');
        }

        return $stock_movement;
    }

    public function getShowItems($stock_movement_id)
    {
        $stock_movement = StockMovement::find($stock_movement_id)->load([
            'product_variations:id,product_id,variation_id,color_id,size_id'
        ]);


        $items = $stock_movement->product_variations()->with("product:id,name,description,material,composition,care_instructions,fit,style,season")->paginate(10);

        if (!$stock_movement) {
            throw new InvalidArgumentException('StockMovement not found');
        }

        return $items;
    }
    // 
    public function createStockMovement($stock_movement_data)
    {
        // $delivery_date = Carbon::create($stock_movement_data['delivery_date']);
        $stock_movement = StockMovement::create([
            "product_variation_id" => $stock_movement_data['product_variation_id'],
            "from_inventory_id" => $stock_movement_data['from_inventory_id'],
            "to_inventory_id" => $stock_movement_data['to_inventory_id'],
            "num_of_packages" => $stock_movement_data['num_of_packages'],
            "delivery_date" => Carbon::create($stock_movement_data['delivery_date']),
            "expected" => $stock_movement_data['expected'],
        ]);

        if (!$stock_movement) {
            throw new Exception('Something Wrong Happend');
        }

        return $stock_movement;
    }

    public function uploadAttachment($stock_movement_data, $stock_movement_id)
    {
        try {
            $stock_movement = StockMovement::find($stock_movement_id)
                ->load('source_inventory', 'destination_inventory');

            $path = 'inventoris/' . $stock_movement->destination_inventory->code; // Specify your desired path here
            // return $path;
            $file = $stock_movement_data['file'];
            $extenstion = $file->getClientOriginalExtension();

            // Move the uploaded file to the desired location
            $file->storeAs($path, $stock_movement->shipment_name . '.' . $extenstion);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateStockMovement(array $data, int $stock_movement_id, int $sku_id, int $location_id): StockMovement
    {
        $stock_movement = StockMovement::find($stock_movement_id);
        $stock_movement->update([
            'sku_id' => $sku_id,
            'location_id' => $location_id,
            'type' => $data['type'],
            'quantity' => $data['quantity'],
        ]);

        if (!$stock_movement) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $stock_movement;
    }

    public function delete(int $stock_movement_id): void
    {
        $stock_movement = StockMovement::find($stock_movement_id);

        if (!$stock_movement) {
            throw new InvalidArgumentException('Stock Movement not found');
        }

        $stock_movement->delete();
    }

    public function forceDelete(int $stock_movement_id): void
    {
        $stock_movement = StockMovement::find($stock_movement_id);

        if (!$stock_movement) {
            throw new InvalidArgumentException('Stock Movement not found');
        }

        $stock_movement->forceDelete();
    }


    // public function searchProduct($shipment_name)
    // {

    //     $shipment_name = request('shipment_name');
    //         $shipment= StockMovement::where('shipment_name', 'like', '%' . $shipment_name . '%')->get();
    //         if (!$shipment) {
    //             throw new InvalidArgumentException('Shipment not found');
    //         }
    //     return  $shipment ;

    // }

    protected function applySort($query, array $sort_data)
    {
        // return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
        return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
    }

    protected function applyFilters(Builder $query, array $filters)
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

    protected function filterBySearch($query, $filter_data)
    {
        // return $query->whereLike('shipment_name', $filter_data['search']);
        $search = $filter_data['search'];
        // dd($search);
        return $query->where('shipment_name', 'like', '%' . $search . '%');
    }

    protected function filterByShipping($query, $filter_data)
    {
        $shipping_min = $filter_data['shipping_min'] ?? now()->format('Y-m-d');
        $shipping_max = $filter_data['shipping_max'] ?? Carbon::now()->addMonth(3);
        return $query->whereBetween('shipped_date', [$shipping_min, $shipping_max]);
    }

    protected function filterByCreate($query, $filter_data)
    {
        $create_min = $filter_data['create_min'] ?? now()->format('Y-m-d');
        $create_max = $filter_data['create_max'] ?? Carbon::now()->addMonth(3);
        return $query->whereBetween('created_at', [$create_min, $create_max]);
    }

    protected function filterByReceived($query, $filter_data)
    {
        $received_min = $filter_data['received_min'] ?? now()->format('Y-m-d');
        $received_max = $filter_data['received_max'] ?? Carbon::now()->addMonth(3);
        return $query->whereBetween('received_date', [$received_min, $received_max]);
    }

    protected function filterByStatus($query, $filter_data)
    {
        return $query->where('status', $filter_data['status']);
    }
}
