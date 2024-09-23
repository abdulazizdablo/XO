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
use App\Services\CargoRequestPVService;
use App\Models\Inventory;
use Illuminate\Support\Str;
use App\Enums\Inventories;
use Exception;





class CargoRequestPVController extends Controller
{


    public function __construct(protected CargoRequestService $cargo_request_service, protected CargoRequestPVService $cargoRequestPVService, protected PaginateCollection $paginateCollection, protected InventoryService $inventoryService)
    {
    }


    public function send(Request $request)
    {
        $employee = auth('api-employees')->user();

        if (!$employee) {

            return response()->error(['message' => 'Unauthinticated'], 400);
        }

        try {
            DB::beginTransaction();


            $cargo_request = $this->cargo_request_service->sendRequest($request->cargo_request, $employee->inventory_id, $employee->id);

            $cargo_request_pv = $this->cargoRequestPVService->sendMany($cargo_request, $request->cargo_request_items);


            DB::commit();

            return response()->success(['request' => $cargo_request, 'request_product_variations' => $cargo_request_pv, 'message' => 'Request has been sent successfully'], 201);
        } catch (Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollback();

            // Handle the error, log it, etc.
            return $e;
        }
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



        if ($employee->hasRole(Roles::OPERATION_MANAGER) || $employee->hasRole(Roles::WAREHOUSE_MANAGER) || $employee->hasRole(Roles::MAIN_ADMIN)) {


              $dateScope = request('date_scope');

              $expected = 0;
        $from_date = null;
        $to_date = null ;
        if ($dateScope == null) {
            $dateScope == 'Today';
        }
            $shipment= CargoShipment::all();
            $modelName = \App\Models\CargoShipment::class;
            $shipments = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)>where('from_inventory',$employee->inventory_id)->count();
            $closed = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('from_inventory',$employee->inventory_id)->where('status','closed')->count();
            $open = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)->where('status','open')->count();

$shipments->cargo_shipment_pv->each(function($item) use($expected){

$expected += $item->sum(whereColumn('quantity','!=', 'recieved'));



});
            
           

        return  [
            'shipments' => $shipments,
           'closed_requests_count' => $closed,
         'open_requests_count' => $open,
         'expected-delivered_requests_count' => $expected
        ];

            
            
            
            
            
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


    public function applyFilters($query, array $filters)
    {
        $appliedFilters = [];
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);
            if (method_exists($this, $method) && isset($value) && !in_array($column_name, $appliedFilters)) {
                $query = $this->{$method}($query, $filters);
                $appliedFilters[] = $column_name;
            }
        }

        return $query;
    }





    public function filterBySearch($query, $filter_data)
    {

        $search = $filter_data['search'] ?? '';
        return $query->where('request_id', 'LIKE', '%' . $search . '%')->orWhere('shipment_name', 'LIKE', '%' . $search . '%');
    }




    public function getLogisticsCargoRequests(Request $request)
    {


        $filter_data = $request->only(['date_send_min', 'date_send_max', 'status', 'search']);
        $warehouse_manager = auth('api-employees')->user();

        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER) || $warehouse_manager->hasRole(Roles::WAREHOUSE_ADMIN) || $warehouse_manager->hasRole(Roles::OPERATION_MANAGER)) {

            $cargo_requests = CargoRequest::with('cargo_requests_pv')->where('to_inventory', $warehouse_manager->inventory_id)->latest();



            if (!empty($filter_data)) {
                $cargo_requests = $this->applyFilters($cargo_requests, $filter_data);
            }
            if ($cargo_requests->count() == 0) {
                throw new Exception('There Is No Requests Yet');
            }
            $cargo_requests = $cargo_requests->paginate(8);


            $all_items = 0;

            $cargo_requests->each(function ($item) use (&$all_items) {


                $item->product_num = $item->cargo_requests_pv()->count();
                $item->from_inventory = $item->inventory()->first()->name;

                $item->cargo_requests_pv->each(function ($cargo_request_item) use (&$all_items) {
                    $all_items += $cargo_request_item->requested_from_inventory;
                });

                $item->all_items = $all_items;
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
        $filter_data = $request->only(['date_send_min', 'date_send_max', 'status', 'search']);

        $warehouse_manager = auth('api-employees')->user();


        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER)) {


            $inventory_id = $warehouse_manager->inventory_id;


            //  $inventory_id = $request->inventory_id;
            $shipments = CargoShipment::with('inventory', 'cargo_request.inventory', 'cargo_request.cargo_requests_pv')->where('to_inventory', $inventory_id)->whereHas('cargo_request')->get();

            // $cargo_requests = CargoRequest::query()->where('to_inventory', $warehouse_manager->inventory_id);


            ///  $cargo_requests = CargoRequest::where()
        
            if (!empty($filter_data)) {
                $shipments = $this->applyFilters($shipments, $filter_data);
            }
            if ($shipments->count() == 0) {
                throw new Exception('There Is No Requests Yet');
            }
            $shipments = $shipments->get();

            $shipments->each(function ($item) {

                $expected = $item->cargo_shipment_pv->sum('quantity');
                $received = $item->cargo_shipment_pv->sum('recieved');


                $item->expected = $expected;
                $item->received = $received;


                $item->source_inventory = $item->cargo_request->inventory->name;
                $item->destination_inventory = $item->inventory->first()->name;
            });

            $shipments = $this->paginateCollection::paginate($shipments, 10, 20);
            return response()->success($shipments, 200);
        } else {

            return response()->error('Unauthorized', 401);
        }
    }

    public function getLogisticsAssignedCargoShipment(Request $request)
    {
        $filter_data = $request->only(['date_send_min', 'date_send_max', 'status', 'search']);

        // Assuming you have some authorization logic here that determines if the user is authorized
        // If not authorized, return an error response
        if (!auth('api-employees')->check()) {
            return response()->error('Unauthorized', 403);
        }
        $warehouse_manager = auth('api-employees')->user();
        //  $inventory_id = $request->inventory_id;

        $shipments = CargoShipment::with('inventory', 'cargo_request.inventory', 'cargo_request.cargo_requests_pv')
            ->where('from_inventory', $warehouse_manager->inventory_id);

        if (!empty($filter_data)) {
            $shipments = $this->applyFilters($shipments, $filter_data);
        }
        if ($shipments->count() == 0) {
            throw new Exception('There Is No Requests Yet');
        }
        $shipments = $shipments->get();
        $shipments->each(function ($item) {

            $expected = $item->cargo_shipment_pv->sum('quantity');
            $received = $item->cargo_shipment_pv->sum('received');

            $item->expected = $expected;
            $item->received = $received;

            $item->source_inventory = $item->cargo_request->inventory->name;
            $item->destination_inventory = $item->inventory->name;
        });

        // Assuming you have a paginateCollection method that handles pagination
        $shipments = $this->paginateCollection::paginate($shipments, 10);

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



        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER)) {



            //$inventory_id = auth('api-employee')->user()->inventory_id;
            //  $inventory_id = $request->inventory_id;
            //  $products_outside_inventory = ProductVariation::with('inventories')->get();

            //    $filtered_products = $products_outside_inventory->filter(function ($product) use ($inventory_id) {
            //    return !$product->inventories->contains('id', $inventory_id);
            //     });



            //  return response()->success($filtered_products, 200);





            $filter_data = $request->only(['status', 'stock', 'date_min', 'date_max', 'sku_code', 'product_name', 'price_min', 'price_max']);

            $inventory_id = $warehouse_manager->inventory_id;



            if (isset($request->first_point_inventory)) {

                $products_first_point_shipped = ProductVariation::with('product', 'product.pricing', )->get();

                $products_defined = $products_first_point_shipped->map(function ($item) {

                    return [


                        'product_variation_id' => $item->id,
                        'name' => $item->name,
                        'sku_code' => $item->sku_code,
                        'stock' => null,
                    ];
                });

                return response()->success(['inventory_name' => 'First Point Warehouse', 'products' => $products_defined], 200);
            }

            $inventory = Inventory::findOrFail($inventory_id);


            $inventory_stock = $this->inventoryService->getInventoryProducts($filter_data, $inventory_id);


            $inventory_data = $inventory_stock
                ->filter(function ($item) use ($inventory_id, $inventory) {
                    // Keep the item only if the current stock level is not  0
                    return $item->stocks()->where('inventory_id', $inventory_id)->first()->current_stock_level != 0;
                })
                ->map(function ($item) use ($inventory_id, $inventory) {
                    $collection = collect([
                        'product_variation_id' => $item->product_variations->first()->id,
                        'name' => $item->name,
                        'sku_code' => $item->product_variations->first()->sku_code,
                        'stock' => $item->stocks()->where('inventory_id', $inventory_id)->first()->current_stock_level,
                        'price' => $item->pricing->value,
                        'status' => $item->stocks->first()->status,
                        'photo' => $item->product_variations->first()->photos->where('photos.color_id', $item->product_variations->first()->color_id)->first(),
                    ]);






                    if ($item->stocks->where('inventory_id', $inventory_id)->first()->current_stock_level <= 10) {
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
