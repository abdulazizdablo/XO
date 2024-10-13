<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shipment\CreateShipmentRequest;
use App\Helpers\StringHelper;
use App\Models\CargoRequest;
use App\Models\CargoShipment;
use App\Models\CargoShipmentPV;
use App\Models\Shipment;
use App\Services\CargoRequestPVService;
use App\Services\CargoShipmentService;
use App\Services\CargoShipmentPVService;
use Illuminate\Support\Str;
use App\Enums\CargoRequestStatus;
use App\Http\Requests\CargoShipment\ShipmentArrivedRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use App\Services\InventoryService;
use App\Enums\Roles;
use App\Models\Inventory;
use App\Models\StockLevel;

class CargoShipmentController extends Controller
{


    public function __construct(protected PaginateCollection $paginate_collection, protected InventoryService $inventoryService)
    {
    }

    public function index(Request $request)
    {

        $cargo_shipments = CargoShipment::with('cargo_shipment_pv', 'cargo_shipment_pv.product_variation')->get();

        // Assuming $cargo_shipments is a collection of cargo shipment models
        $inventoryIds = $cargo_shipments->pluck('from_inventory')->merge($cargo_shipments->pluck('to_inventory'))->unique();

        // Retrieve Inventory records based on the combined list of IDs
        $inventoryNamesById = Inventory::whereIn('id', $inventoryIds)->pluck('name', 'id');

        $cargo_shipments->each(function ($item) use ($inventoryNamesById) {
            // Replace 'from_inventory' with 'source_inventory' and set its value to the inventory name
            $item->source_inventory = $inventoryNamesById[$item->from_inventory] ?? null;
            // Set 'from_inventory' to null instead of unsetting it
            unset ($item['from_inventory']);

            // Retrieve the inventory name for 'to_inventory' and set it to 'destination_inventory_inventory'
            $item->destination_inventory = $inventoryNamesById[$item->to_inventory] ?? null;
            // Set 'to_inventory' to null instead of unsetting it
            unset ($item['to_inventory']);
        });

        return response()->success($cargo_shipments, 200);
    }

    public function confirmShipment(Request $request)
    {			
			


        $cargo_shipment = CargoShipment::findOrFail($request->cargo_shipment_id)->load('cargo_shipment_pv');

		

		
		
		
        if ($cargo_shipment->status == 'open') {

            $cargo_shipment->update(['status' => 'pending', 'sender_packages' => $request->sender_packages ]);
					foreach($cargo_shipment->cargo_shipment_pv as $item){
		$stock_level = StockLevel::where('inventory_id',$cargo_shipment->from_inventory)->where('product_variation_id',$item['product_variation_id'])->first();

			
		$stock_level->update(['on_hold' => $stock_level->current_stock_level]);
		

            return response()->success(['message' => 'Shipment has been sent successfully', 'cargo_shipment' => $cargo_shipment], 200);
						
					}
        }
	}
    
    public function show(Request $request)
    {

        $cargo_shipment = CargoShipment::findOrFail($request->id)->with('cargo_shipment_pv', 'cargo_shipment_pv.product_variation')->paginate(10);


        return response()->success($cargo_shipment, 200);
    }

    public function delete(Request $request)
    {

        $cargo_shipment = CargoShipment::findOrFail($request->cargo_shipment_id);
        $cargo_shipment->deleted_at = now();
        $cargo_shipment->save();
        return response()->success(['message' => 'Cargo Shipment has been deleted successfully'], 200);
    }

    public function arrived(Request $request)
    {

        try {


            DB::beginTransaction();
            $cargo_request_shipment = CargoShipment::findOrFail($request->cargo_shipment_id)->load('cargo_shipment_pv','inventory');

            $cargo_request_shipment->cargo_shipment_pv->each(function ($item) use ($request) {

                $quantity = collect($request->items_received)->where('product_variation_id', $item->product_variation_id)->first()['quantity'];
				
				
                $item->update(['received' => $quantity]);
				
				dd($item->inventory->stock_level());
				
				
				
				
			/*	dd($item->inventory->stock_level()->where('product_variation_id',$item->product_variation_id));
				$item->inventory->stock_level()->where('product_variation_id',$item->product_variation_id)->first()->current_stock_level -= $request->items_received)->where('product_variation_id', $item->product_variation_id)->first()['quantity'];
				
*/
			 });
			
  $cargo_request_shipment->received_date = now();
			$cargo_request_shipment->save();

           // $cargo_request_shiped = CargoRequest::findOrFail($request->request_id);
           // $cargo_request_shiped->update(['status' => 'closed', 'request_status_id' => CargoRequestStatus::CLOSED]);

          
            DB::commit();
            return response()->success(['message' => 'Shipment has been deliverd successfully'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->error($e->getMessage(), 400);
        }
    }

    public function shipmentDetailsItems(Request $request)
    {


        $shipment = CargoShipment::findOrFail($request->cargo_shipment_id)->load('cargo_request', 'cargo_request.cargo_requests_pv','inventory');
        // CargoShipment::where('cargo_request_id',$cargo_request_shipment->id)->first());
        $cargo_request_shipment = $shipment->cargo_request;
        $cargo_request_shipment_items = $shipment->cargo_shipment_pv;

        $send_items = 0;
        $from_inventory = $shipment->inventory?->name ?? "First Point";

        $to_inventory = $cargo_request_shipment?->inventory()->first()->name ?? $shipment->to_inventory()->first()->name;
        $cargo_request_shipment_items->each(function ($request_product_item) use (&$send_items, $shipment, $cargo_request_shipment, $cargo_request_shipment_items, $from_inventory, $to_inventory) {

            if (is_null($cargo_request_shipment)) {


                $send_items += $shipment->cargo_shipment_pv()
                    ->whereIn('product_variation_id', $request_product_item->pluck('product_variation_id'))
                    ->sum('quantity');
            } else {

                $send_items += $cargo_request_shipment->cargo_requests_pv()
                    ->whereIn('product_variation_id', $request_product_item->pluck('product_variation_id'))
                    ->sum('requested_from_manager');
            }

            $cargo_request_shipment_items->each(function ($item) use ($send_items, $from_inventory, $to_inventory, $cargo_request_shipment,$shipment) {
                $product =  //Product::with('product_variation:sku_code')->where('product_variation_id', $item->product_variation_id)->first();
                    $item->product_variation()->first()->product()->first();




                $photo = $item->product_variation->photos()->where('photos.color_id', $item->product_variation->color_id)->first();
                $item->product_name = $product->name;
                $item->sku_code = $item->product_variation->sku_code;
                $item->desired_items = $cargo_request_shipment?->cargo_requests_pv->where('product_variation_id', $item->product_variation_id)->first()->requested_from_inventory ?? null;
                $item->items_be_sent = $item->quantity;
                $item->items_received = $cargo_request_shipment?->cargo_requests_pv->where('product_variation_id', $item->product_variation_id)->first()->requested_from_manager ?? $shipment->cargo_shipment_pv()->where('product_variation_id', $item->product_variation_id)->first()->received;


                $item->source_inventory = $from_inventory;
                $item->destination_inventory = $to_inventory;
                $item->photos = $photo;


                // Additional logic if needed...
            });
        });


        $cargo_request_shipment_items = $this->paginate_collection::paginate($cargo_request_shipment_items, 10, 20);

        return response()->success([

            'shipment_items' => $cargo_request_shipment_items,
            'status' => $shipment->status

        ], 200);
    }
    public function requestDetails(Request $request)
    {

        $cargo_request = CargoRequest::findOrFail($request->cargo_request_id)->load('inventory');


        $cargo_request_shipment = CargoShipment::where('cargo_request_id', $cargo_request->id)->first();



        if (!$cargo_request_shipment) {

            $request_by = [
                'request_by' => [

                    'warehouse' => $cargo_request->inventory->name,
                    'date_created' => $cargo_request->created_at->format('Y-m-d H:i:s'),
                    'status' => $cargo_request->status,
                    'request_id' => $cargo_request->request_id,
                    'inventory_destination_id' => $cargo_request->inventory()->first()->id


                ]


            ];

            return response()->success($request_by, 200);
        } else if (!$cargo_request) {

            $request_by = [
                'request_by' => [


                    'warehouse' => $cargo_request->inventory->name,
                    'date_created' => $cargo_request->created_at->format('Y-m-d H:i:s'),
                    'status' => $cargo_request->status,
                    'request_id' => $cargo_request->request_id,
                    'inventory_destination_id' => $cargo_request->inventory()->first()->id


                ],

                'shipped_from' => [




                    'warehouse' => $cargo_shipment->inventory?->name ?? "First Point",
                    'date_created' => $cargo_request_shipment->created_at->format('Y-m-d H:i:s'),
                    'status' => $cargo_request_shipment->status,
                    'shipment_name' => $cargo_request_shipment->shipment_name



                ]


            ];


            return response()->success([$request_by, 'status' => $cargo_request_shipment->status], 200);
        }
    }



    public function requestAndShipmentDetails(Request $request)
    {

        $cargo_shipment = CargoShipment::findOrFail($request->cargo_shipment_id)->load('cargo_request', 'cargo_request.inventory', 'inventory');

        if ($cargo_shipment->cargo_request) {



            $cargo_request = $cargo_shipment->cargo_request;

            $shipment_details = [


                'request_by' => [

                    'warehouse' => $cargo_request->inventory->name,
                    'date_created' => $cargo_request->created_at->format('Y-m-d H:i:s'),
                    'status' => ucfirst($cargo_shipment->status),
                    'request_id' => $cargo_request->request_id


                ],

                'shipped_from' => [

                    'warehouse' => $cargo_shipment->inventory?->name ?? "First Point",
                    'date_created' => $cargo_shipment->created_at->format('Y-m-d H:i:s'),
                    'status' => ucfirst($cargo_shipment->status),
                    'shipment_name' => $cargo_shipment->shipment_name,
                    'date_received' => $cargo_shipment->received_date?->format('Y-m-d H:i:s')


                ]


            ];

            return response()->success($shipment_details, 200);
        } else {

            $shipment_details = [


                'shipped_from' => [

                    'warehouse' => $cargo_shipment->inventory?->name ?? "First Point",
                    'date_created' => $cargo_shipment->created_at->format('Y-m-d H:i:s'),
                    'status' => ucfirst($cargo_shipment->status),
                    'shipment_name' => $cargo_shipment->shipment_name,
                    'date_received' => $cargo_shipment->received_date?->format('Y-m-d H:i:s') ?? "Not Recieved"


                ]

            ];

            return response()->success($shipment_details, 200);
        }
    }

    public function requestDetailsItems(Request $request)
    {

        $cargo_request = CargoRequest::findOrFail($request->cargo_request_id)->load('cargo_requests_pv', 'inventory');


        $cargo_request_items = $cargo_request->cargo_requests_pv->values();


        $cargo_request_items->each(function ($item) use ($cargo_request) {

            $product =  //Product::with('product_variation:sku_code')->where('product_variation_id', $item->product_variation_id)->first();
                $item->product_variation()->first()->product()->first();


            /*   if(!$product){
                   
                   dd(   $item->product_variation()->first());
                   
               }
               */

            $photo = $item->product_variation->photos()->where('photos.color_id', $item->product_variation->color_id)->first();
            $item->product_name = $product->name;
            $item->sku_code = $item->product_variation->sku_code;
            $item->desired_items = $item->requested_from_inventory;
            $item->to_inventory = $cargo_request->inventory->name;
            $item->photo = $photo;
        });
        //return $cargo_request_items;

        //$cargo_request_items = $cargo_request_items->values();

        $cargo_request_items = $this->paginate_collection::paginate($cargo_request_items, 10);
        //$cargo_request_items = array_values($cargo_request_items->all());

        //$cargo_request_items = $cargo_request_items->getCollection();


        return response()->success([

            'request_items' => $cargo_request_items


        ], 200);
    }


    /*   public function requestDetails(Request $request)
     {


     
     
     
     
     
 }*/

    public function getAllinventories(Request $request)
    {

        $employee = auth('api-employees')->user();



        if ($employee->hasRole(Roles::OPERATION_MANAGER)) {


            $inventories = $this->inventoryService->getAllInventories();


            return response()->success($inventories, 200);
        } else
            return response()->error(['message' => 'Unauthorized'], 403);
    }

    public function getAllShipments(Request $request)
    {

        $filter_data = $request->only(['date_created_min','date_created_max','date_send_min', 'date_send_max','date_received_min','date_received_max', 'status', 'search']);

        $employee = auth('api-employees')->user();
        if (!$employee) {
            return response()->error(['message' => 'Unauthorized'], 403);
        }

        if ($employee->hasRole(Roles::OPERATION_MANAGER) || $employee->hasRole(Roles::MAIN_ADMIN)) {


            $shipments = CargoShipment::query();



            if (!empty($filter_data)) {
                $shipments = $this->applyFilters($shipments, $filter_data);
            }
            if ($shipments->count() == 0) {
                return response()->success([], 204);
            }

			
			
			

            $shipments = $shipments->latest()->get();
			$shipments->each(function($item){
				
				
				
			
			
			$item->source_inventory = $item->inventory()->first()->name ?? 'First Point Inventory';
		$item->destination_inventory = $item->to_inventory()->first()->name ?? null;
			
				$item->expected = (int)$item->cargo_shipment_pv()->sum('quantity');
				
									$item->received = $item->cargo_shipment_pv()->sum('received');
			});
			
			$shipments = $this->paginate_collection::paginate($shipments,10);


            return response()->success($shipments, 200);
        } else
            return response()->error(['message' => 'Unauthorized'], 403);



        //  $inventory_id = 


    }




  public function applyFilters($query, array $filters)
{
    $appliedFilters = [];

    foreach ($filters as $attribute => $value) {
        $parts = explode('_', $attribute);

        if (count($parts) >= 2) {
            $datePart = Str::studly($parts[0]);
            $createdPart = Str::studly($parts[1]);
            $attribute = $datePart . $createdPart;
			
        } else {
            $attribute = Str::studly($attribute);
					

        }

        $method = 'filterBy' . $attribute;

        if (method_exists($this, $method) && isset($value) && !in_array($attribute, $appliedFilters)) {
            $query = $this->{$method}($query, $filters);
            $appliedFilters[] = $attribute;
        }
    }

    return $query;
}



    public function filterByDateSend($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? 0;
        $date_max = $filter_data['date_max'] ?? date('Y-m-d');

        return $query->whereBetween('created_at', [$date_min, $date_max]);
    }
	
	
	
	    public function filterByDateCreated($query, $filter_data)
    {
        $date_min = $filter_data['date_created_min'] ?? 0;
        $date_max = $filter_data['date_created_max'] ?? date('Y-m-d');

        return $query->whereBetween('created_at', [$date_min, $date_max]);
    }

	

    public function filterByStatus($query, $filter_data)
    {
        return $query->where('status', $filter_data['status']);
    }

    public function filterByDateShiped($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? 0;
        $date_max = $filter_data['date_max'] ?? date('Y-m-d');

        return $query->whereBetween('ship_date', [$date_min, $date_max]);
    }

    public function filterByDateRecieved($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? 0;
        $date_max = $filter_data['date_max'] ?? date('Y-m-d');

        return $query->cargo_request()->where('request_status_id', CargoRequestStatus::CLOSED)->whereBetween('recieved_date', [$date_min, $date_max]);
    }

    public function filterBySearch($query, $filter_data)
    {

        $search = $filter_data['search'] ?? '';
        return $query->where('request_id', 'LIKE', '%' . $search . '%')->orWhere('shipment_name', 'LIKE', '%' . $search . '%');
    }



    public function importProduct(Request $request)
    {

        return $products_variations = ProductVariation::all();
    }

    public function getAllLogisticsCount(Request $request)
    {
    }
}
