<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CargoRequest\SendRequestRequest;
use Illuminate\Http\Request;
use App\Models\CargoRequest;
use App\Services\CargoRequestService;
use App\Enums\CargoRequestStatus;
use App\Models\CargoRequestPV;
use App\Models\CargoShipment;
use App\Models\ProductVariation;
use App\Utils\PaginateCollection;
use App\Models\Product;
use App\Enums\Roles;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;
use App\Models\Inventory;
use Illuminate\Support\Str;
use App\Enums\Inventories;
use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Models\StockLevel;




class CargoRequestController extends Controller
{


    public function __construct(protected CargoRequestService $cargo_request_service, protected PaginateCollection $paginateCollection, protected InventoryService $inventoryService)
    {
    }



    public function cancel(Request $request)
    {

        $cargo_request = CargoRequest::findOrFail($request->cargo_request);

        $cargo_request->update(['status' => 'canceled', 'request_status_id' => CargoRequestStatus::CANCELED]);


        return response()->success(['canceled_request' => $cargo_request, 'message' => 'The Request has been cancelled successfully'], 200);
    }

    public function requestCount()
    {
        $employee = auth('api-employees')->user();

        $data = [];

        if ($employee->hasRole(Roles::OPERATION_MANAGER) || $employee->hasRole(Roles::WAREHOUSE_MANAGER) || $employee->hasRole(Roles::MAIN_ADMIN)) {


            $dateScope = request('dateScope');
            $from_date = null;
            $to_date = null;
            if ($dateScope == null) {
                $dateScope == 'Today';
            }
            $shipment = CargoShipment::all();
            $modelName = \App\Models\CargoShipment::class;
            $shipments = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->count();
            $closed = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status', 'closed')->count();
            $open = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status', 'open')->count();
            $expected = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status', 'closed')->whereHas('cargo_shipment_pv', function ($query) {

                $query->whereColumn('received', '!=', 'quantity');
            })->count();

            $data = [
                'shipments' => $shipments,
                'closed' => $closed,
                'open' => $open,
                'expected' => $expected,
            ];
            
            // Store the array into the cache
            // The first argument is the cache key, and the second argument is the data to be cached
            // The third argument is the cache duration in minutes
            Cache::remember('shipment_count', 60 * 14, function () use ($data) {

                return $data;
                
            });
            


            $logistics_count = [
                'closed_requests_count' => $closed,
                'open_requests_count' => $open,
                'arrived_requests_count' => $shipments,
                'expected_delivered_requests_count' => $expected
            ];

            return response()->success($logistics_count, 200);
        }

        if ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {


            $closed_requests_count = CargoShipment::where('to_inventory', $employee->inventory_id)->where('status', 'closed')->count();
            $open_requests_count = CargoShipment::where('to_inventory', $employee->inventory_id)->where('status', 'open')->count();

            // Calculate Request which does not match the original


            $cargo_shipments = CargoShipment::with('cargo_shipment_pv', 'cargo_request', 'cargo_request.cargo_requests_pv')->where('to_inventory', $employee->inventory_id)->get();

            $expected_delivered_requests_count = 0;


            foreach ($cargo_shipments as $item) {

                foreach ($item->cargo_shipment_pv as $cargo_shipment_pv) {

                    if (!$item->cargo_request && $cargo_shipment_pv->quantity !== $cargo_shipment_pv->received) {


                        $expected_delivered_requests_count++;
                    } else if (!$item->cargo_request->cargo_requests_pv->where('product_variation_id', $cargo_shipment_pv->product_variation_id)->first()) {

                        $expected_delivered_requests_count++;
                    } else if ($cargo_shipment_pv->quantity !== $item->cargo_request->cargo_requests_pv->where('product_variation_id', $cargo_shipment_pv->product_variation_id)->first()->requested_from_inventory) {


                        $expected_delivered_requests_count++;
                    }
                }
            }
            $arrived_requests_count = $closed_requests_count + $expected_delivered_requests_count;

            return response()->success([

                'closed_requests_count' => $closed_requests_count,
                'open_requests_count' => $open_requests_count,
                'arrived_requests_count' => $arrived_requests_count,
                'expected_delivered_requests_count' => $expected_delivered_requests_count

            ], 200);
        } else {

            $closed_requests_count = CargoShipment::where('status', 'closed')->count();
            $open_requests_count = CargoShipment::where('status', 'open')->count();


            // Calculate Request which does not match the original


            $cargo_shipments = CargoShipment::with('cargo_shipment_pv', 'cargo_request', 'cargo_request.cargo_requests_pv')->get();

            $expected_delivered_requests_count = 0;


            foreach ($cargo_shipments as $item) {

                foreach ($item->cargo_shipment_pv as $cargo_shipment_pv) {

                    if (!$item->cargo_request && $cargo_shipment_pv->quantity !== $cargo_shipment_pv->received) {


                        $expected_delivered_requests_count++;
                    } else if (!$item->cargo_request->cargo_requests_pv->where('product_variation_id', $cargo_shipment_pv->product_variation_id)->first()) {

                        $expected_delivered_requests_count++;
                    } else if ($cargo_shipment_pv->quantity !== $item->cargo_request->cargo_requests_pv->where('product_variation_id', $cargo_shipment_pv->product_variation_id)->first()->requested_from_inventory) {


                        $expected_delivered_requests_count++;
                    }
                }
            }
            $arrived_requests_count = $closed_requests_count + $expected_delivered_requests_count;

            return response()->success([

                'closed_requests_count' => $closed_requests_count,
                'open_requests_count' => $open_requests_count,
                'arrived_requests_count' => $arrived_requests_count,
                'expected_delivered_requests_count' => $expected_delivered_requests_count

            ], 200);
        }
    }



    public function filterByDateSend($query, $filter_data)
    {
        $date_min = $filter_data['date_send_min'] ?? 0;
        $date_max = $filter_data['date_send_max'] ?? date('Y-m-d');

        return $query->whereBetween('created_at', [$date_min, $date_max]);
    }

    public function filterByStatus($query, $filter_data)
    {

        return $query->where('status', $filter_data['status']);
    }

    public function filterByDateShipped($query, $filter_data)
    {
        $date_min = $filter_data['date_shipped_min'] ?? 0;
        $date_max = $filter_data['date_shipped_max'] ?? date('Y-m-d');

        return $query->whereBetween('ship_date', [$date_min, $date_max]);
    }

    public function filterByDateRecieved($query, $filter_data)
    {
        $date_min = $filter_data['date_recieved_min'] ?? 0;
        $date_max = $filter_data['date_recieved_max'] ?? date('Y-m-d');

        return $query->cargo_request()->where('request_status_id', CargoRequestStatus::CLOSED)->whereBetween('recieved_date', [$date_min, $date_max]);
    }

    public function filterBySearch($query, $filter_data)
    {

        $search = $filter_data['search'] ?? '';
        $routeName = Route::currentRouteName();

        if ($routeName == 'dashboard.cargo-request.all-requests') {

            return $query->where('request_id', 'LIKE', '%' . $search . '%');
        } else {
            return $query->where('shipment_name', 'LIKE', '%' . $search . '%');
        }
    }


    public  function delete(Request $request)
    {

        $cargo_request = CargoRequest::findOrFail($request->cargo_request_id);
        $cargo_request->deleted_at = now();
        $cargo_request->save();
        return response()->success(['message' => 'Cargo Request has been deleted successfully'], 200);
    }
    public function applyFilters($query, array $filters, $route_name = null)
    {
        $appliedFilters = [];
        foreach ($filters as $attribute => $value) {
            $segments = explode('_', $attribute);
            if (count($segments) >= 2) {
                $firstTwoSegments = $segments[0] . '_' . $segments[1];
            } else {
                // Handle the case where there are less than two segments
                // For example, you might want to use the entire attribute as the column name
                $firstTwoSegments = $attribute;
            }

            $method = 'filterBy' . Str::studly($firstTwoSegments);

            if (method_exists($this, $method) && isset($value) && !in_array($firstTwoSegments, $appliedFilters)) {
                $query = $this->{$method}($query, $filters);
                $appliedFilters[] = $firstTwoSegments;
            }
        }

        return $query;
    }
	
	public function importProductByProductVariation(Request $request){
	
	$product_variation_id = $request->product_variation_id;
		$product_stock_level_inventories = StockLevel::where('product_variation_id',$product_variation_id)->pluck('inventory_id');
		
		$inventories = Inventory::findOrFail($product_stock_level_inventories);
		
		return response()->success($inventories,200);
	
	}

	
	


    public function getLogisticsCargoRequests(Request $request)
    {


        $filter_data = $request->only(['date_send_min', 'date_send_max', 'status', 'search']);
        $warehouse_manager = auth('api-employees')->user();
        // $routeName = Route::currentRouteName();
        if ($warehouse_manager->hasRole(Roles::OPERATION_MANAGER)) {

            $cargo_requests = CargoRequest::with('cargo_request_pv')->latest();

            ///  $cargo_requests = CargoRequest::where()
            if ($cargo_requests->count() == 0) {
                return response()->success(['message' => 'There is no Requests Yet'], 200);
            }
            if (!empty($filter_data)) {
                $cargo_requests = $this->applyFilters($cargo_requests, $filter_data);
            }

            $cargo_requests = $cargo_requests->get();


            $all_items = 0;

            $cargo_requests->each(function ($item) use (&$all_items) {


                $item->product_num = $item->cargo_requests_pv()->count();
                $item->from_inventory = $item->inventory()->first()->name;

                $item->cargo_requests_pv->each(function ($cargo_request_item) use (&$all_items) {
                    $all_items += $cargo_request_item->requested_from_inventory;
                });

                $item->all_items = $all_items;
                $all_items = 0;
            })->values();

            // Now, $cargo_requests collection has 'product_num' added to each item

            /*  $response_data = $cargo_requests->map(function ($item) {


            return [

                'date_send' => $item->ship_date,
                'from' => $item->to_inventory,
                'num_of_products' => $ite


            ];
        });*/

            $cargo_requests = $this->paginateCollection::paginate($cargo_requests, 10);
            return response()->success($cargo_requests->toArray(), 200);
        }

        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER) || $warehouse_manager->hasRole(Roles::WAREHOUSE_ADMIN) || $warehouse_manager->hasRole(Roles::OPERATION_MANAGER)) {

            $cargo_requests = CargoRequest::where('to_inventory', $warehouse_manager->inventory_id)->latest();

            ///  $cargo_requests = CargoRequest::where()
            if ($cargo_requests->count() == 0) {
                return response()->success(['message' => 'There is no Requests Yet'], 200);
            }
            if (!empty($filter_data)) {
                $cargo_requests = $this->applyFilters($cargo_requests, $filter_data);
            }

            $cargo_requests = $cargo_requests->get();


            $all_items = 0;

            $cargo_requests->each(function ($item) use (&$all_items) {


                $item->product_num = $item->cargo_requests_pv()->count();
                $item->from_inventory = $item->inventory()->first()->name;

                $item->cargo_requests_pv()->each(function ($cargo_request_item) use (&$all_items) {
                    $all_items += $cargo_request_item->requested_from_inventory;
                });

                $item->all_items = $all_items;
                $all_items = 0;
            })->values();

            // Now, $cargo_requests collection has 'product_num' added to each item

            /*  $response_data = $cargo_requests->map(function ($item) {


            return [

                'date_send' => $item->ship_date,
                'from' => $item->to_inventory,
                'num_of_products' => $ite


            ];
        });*/

            $cargo_requests = $this->paginateCollection::paginate($cargo_requests, 10);
            return response()->success($cargo_requests->toArray(), 200);
        } else {

            return response()->error('Unauthorized', 401);
        }
    }

    public function getLogisticsMyCargoShipment(Request $request)
    {




        $filter_data = $request->only([
            'date_created_min',
            'date_created_max',
            'date_shipped_min',
            'date_shipped_max',
            'date_received_min',
            'date_received_max',
            'status',
            'search'
        ]);

        $warehouse_manager = auth('api-employees')->user();



        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER)) {



            $inventory_id = $warehouse_manager->inventory_id;




            //  $inventory_id = $request->inventory_id;
            $shipments = CargoShipment::with('inventory', 'cargo_request.inventory', 'cargo_request.cargo_requests_pv')->where('to_inventory', $inventory_id)->latest();

            // $cargo_requests = CargoRequest::query()->where('to_inventory', $warehouse_manager->inventory_id);


            ///  $cargo_requests = CargoRequest::where()

            if (!empty($filter_data)) {
                $shipments = $this->applyFilters($shipments, $filter_data);
            }

            if ($shipments->count() == 0) {
                return response()->success(['message' => 'There is no Shipments Yet'], 204);
            }


            $shipments = $shipments->get();


            $shipments->each(function ($item) {

                if (!is_null($item->cargo_request)) {

                    $expected = $item->cargo_request->cargo_requests_pv->sum('requested_from_inventory');
                    $received = $item->cargo_request->cargo_requests_pv->sum('requested_from_manager');
                } else {


                    $expected = $item->cargo_shipment_pv->sum('quantity');
                    $received = $item->cargo_shipment_pv->sum('received');
                }


                //   $expected = $item->cargo_request->cargo_requests_pv->sum('requested_from_inventory');
                // $received = $item->cargo_request->cargo_requests_pv->sum('requested_from_manager');


                $item->expected = $expected;
                $item->received = $received;


                $item->destination_inventory = $item->cargo_request->inventory->name ?? $item->to_inventory()->first()->name;

                $item->source_inventory = $item->inventory->name ?? "First Point Warehouse";
            });

            $shipments = $this->paginateCollection::paginate($shipments, 10);
            return response()->success($shipments, 200);
        } else {

            return response()->error('Unauthorized', 401);
        }
    }

    public function getLogisticsAssignedCargoShipment(Request $request)
    {
        $filter_data = $request->only([
            'date_created_min',
            'date_created_max',
            'date_shipped_min',
            'date_shipped_max',
            'date_received_min',
            'date_received_max',
            'status',
            'search'
        ]);
        $routeName = Route::currentRouteName();

        // Assuming you have some authorization logic here that determines if the user is authorized
        // If not authorized, return an error response
        if (!auth('api-employees')->check()) {
            return response()->error('Unauthorized', 403);
        }
        $warehouse_manager = auth('api-employees')->user();
        //  $inventory_id = $request->inventory_id;

        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER)) {

            $shipments = CargoShipment::with('inventory', 'cargo_request.inventory', 'cargo_request.cargo_requests_pv','cargo_shipment_pv')->latest()
                ->where('from_inventory', $warehouse_manager->inventory_id)
                ->latest();


            if (!empty($filter_data)) {
                $shipments = $this->applyFilters($shipments, $filter_data);
            }

            if ($shipments->count() == 0) {
                return response()->success(['message' => 'There is no Shipments Yet'], 204);
            }

            $shipments = $shipments->get();
            $shipments->each(function ($item) {
                $expected = $item->cargo_shipment_pv->sum('quantity');
                $received = $item->cargo_shipment_pv->sum('received');;

                $item->expected = $expected;
                $item->received = $received;

                $item->destination_inventory = $item->to_inventory()->first()->name;
                $item->source_inventory = $item->inventory->name;
            });

            // Assuming you have a paginateCollection method that handles pagination
            $shipments = $this->paginateCollection::paginate($shipments, 10, 20);
        }
        return response()->success($shipments, 200);
    }

    public function search(Request $request)
    {

        $search = '%' . $request->search . '%';


        $searched_products = ProductVariation::where('sku_code', $search)->get();

        return response()->success($searched_products, 200);
    }


    public function importProduct(Request $request)
    {
        $warehouse_manager = auth('api-employees')->user();

        $inventory_id = $request->inventory_id;

        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER) || $warehouse_manager->hasRole(Roles::OPERATION_MANAGER)) {



            //$inventory_id = auth('api-employee')->user()->inventory_id;
            //  $inventory_id = $request->inventory_id;
            //  $products_outside_inventory = ProductVariation::with('inventories')->get();

            //    $filtered_products = $products_outside_inventory->filter(function ($product) use ($inventory_id) {
            //    return !$product->inventories->contains('id', $inventory_id);
            //     });



            //  return response()->success($filtered_products, 200);





            $filter_data = $request->only(['status', 'stock', 'date_min', 'date_max', 'sku_code', 'product_name', 'price_min', 'price_max']);

            //  $inventory_id = $warehouse_manager->inventory_id;

            if ($inventory_id == 'first_point') {

$products_first_point_shipped = ProductVariation::select('id','product_id','sku_code','color_id')
    ->with([
        
        'product.pricing','product',
        'stock_levels',
		'photos'
    ])
    ->get();
				
			//	dd($products_first_point_shipped);
			//	return($product_variations->first()->photos);
				
			//	dd($products_first_point_shipped);

                $products_defined = $products_first_point_shipped->map(function ($item) {
//dd($item->product);
                    return [


  		
						
						'product_variation_id' => $item->id,
                        'name' => $item->product?->name,
                        'sku_code' => $item->sku_code,
// Assuming you've already eager loaded the relationship
'stock' => $item->stock_levels()->exists()
    ? (int) $item->stock_levels->sum('current_stock_level')
   : 0,

                        'price' => $item->product?->pricing->value,
                      //  'status' => $item->stock_levels?->first()->status,
                       'photo' => $item->product?->photosByColorId((array)$item->color_id)->first(),
                    ];
                });

                return response()->success(['inventory_name' => 'First Point Warehouse', 'products' => $products_defined], 200);
            }

            $inventory = Inventory::findOrFail($inventory_id);


  $inventory_stock = $this->inventoryService->getInventoryProductVariations($filter_data, $inventory_id);
			//      $inventory_stock = $this->inventoryService->getInventoryProducts($filter_data, $inventory_id);


//dd(ProductVariation::with('photos')->where('id',1)->get());

            $inventory_data = $inventory_stock
                ->filter(function ($item) use ($inventory_id, $inventory) {
                    // Keep the item only if the current stock level is not  0
                    return $item->stock_levels()->where('inventory_id', $inventory_id)->first()->current_stock_level != 0;
                })
                ->map(function ($item) use ($inventory_id, $inventory) {
					
				//	$color_ids = $item->product_variations()->pluck('color_id');
                    $collection = collect([
                        'product_variation_id' => $item->id,
                        'name' => $item->product()->first()->name,
                        'sku_code' => $item->sku_code,
                        'stock' => $item->stock_levels()->where('inventory_id', $inventory_id)->first()->current_stock_level,
                        'price' => $item->product()->first()->pricing->value,
                        'status' => $item->stock_levels->first()->status,
                        'photo' => $item->product()->first()->photos()->where('color_id',$item->color_id)->first(),
                    ]);






                    if ($item->stock_levels->where('inventory_id', $inventory_id)->first()->current_stock_level <= 10) {
                        $collection->put('raise', true);
                    } else {
                        $collection->put('raise', false);
                    }

                    return $collection;
                })->values();


            //  $inventory_data['inventory_name'] =  $inventory->name;

            return response()->success(['inventory_name' => $inventory->name, 'products' => $inventory_data->values()], 200);


            // $filtered_products now contains only the products without the specified inventory_id

            // Rest of your code...
        } else {

            return response()->error('Unauthorized', 401);
        }
    }
}
