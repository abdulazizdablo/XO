<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Inventories;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipment\CreateShipmentRequest;
use App\Models\CargoRequest;
use App\Models\CargoShipment;
use App\Models\CargoShipmentPV;
use App\Models\FcmToken;
use App\Models\Shipment;
use App\Models\StockLevel;
use App\Services\CargoRequestPVService;
use App\Services\CargoShipmentService;
use App\Services\CargoShipmentPVService;
use App\Enums\CargoRequestStatus;
use App\Enums\Roles;
//use App\Http\Requests\CargoShipment\ShipmentArrivedRequest;
use App\Traits\FirebaseNotificationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\ProductVariation;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class CargoShipmentPVController extends Controller
{
    use FirebaseNotificationTrait;

    //  protected $notified_employees;
    public function __construct(
        protected CargoShipmentService $cargo_shipment_service,
        protected CargoShipmentPVService $cargo_shipment_pv_service,
        protected CargoRequestPVService $cargo_request_pv_service
    ) {
    }


    public function send(Request $request)
    {

        $employee = auth('api-employees')->user();
		
        // $this->cargo_shipment_pv_service->calculateNewStock($cargo_shipment, $request->cargo_shipment_items,$request['cargo_shipment']['from_inventory']);

        if (!$employee) {
            return response()->error('Unauthorized', 401);
        } else if ($employee->hasRole(Roles::WAREHOUSE_ADMIN)) {
            return response()->error('Permission denied', 401);
        }
		
		
		if($request->cargo_shipment['to_inventory'] == $request->cargo_shipment['from_inventory']  ){
		
		return response()->error('inventories are the same',400);
		}

        $cargo_shipment = new CargoShipment();

        if ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
            $cargo_shipment->from_inventory = $employee->inventory_id;
        }
		
        $shipment_name = 'TW-' . rand(10000, 99999) . rand(10000, 99999);
        //  $cargo_shipment->employee_id = auth('api-employees')->user()->id;
        $inventory = Inventory::findOrFail($request->cargo_shipment['to_inventory']);
        $cargo_shipment->to_inventory = $inventory->id;

        if (isset($request->cargo_shipment['from_inventory']) && $request->cargo_shipment['from_inventory'] == 'first_point') {
            // $cargo_shipment = new CargoShipment();
            // $shipment_name = 'TW-' . rand(10000, 99999) . rand(10000, 99999);
            //  $employee_id = auth('api-employee')->user()->id;
            $cargo_shipment->to_inventory =  Inventories::ALEPPO;
            $cargo_shipment->from_inventory = null;

            //unset($request->cargo_shipment['from_inventory']);
            //$cargo_shipment->fill($request->cargo_shipment);
            //$cargo_shipment->employee_id  = $employee_id;
            $cargo_shipment->status = 'closed';
            $cargo_shipment->shipment_name = $shipment_name;
            //  $cargo_shipment->employee_id = auth('api-employees')->user()->id;
            $cargo_shipment->save();
		//	dd($request->cargo_shipment['cargo_shipment_items']);
            $product_variations_ids = array_column($request->cargo_shipment, 'product_variation_id');
            //$product_variations = ProductVariation::findOrFail($product_variations_ids);
            foreach ($request->cargo_shipment_items as $key => $item) {
				$product_variation = [];
			    $stockLevel = StockLevel::where('inventory_id', Inventories::ALEPPO)
                            ->where('product_variation_id', $item['product_variation_id'])
                            ->first();
				
				
			  /*  if ($stockLevel) {
					// Update existing stock level
					$stockLevel->update(['current_stock_level' => $stockLevel->current_stock_level + $item['quantity']]);
				} */if(!$stockLevel) {
					// Create new stock level for new items
					$stockLevel = StockLevel::create([
						'product_variation_id' => $item['product_variation_id'],
						'inventory_id' => Inventories::ALEPPO,
						'name' => 'Initial Shipment',
						'min_stock_level' => 3,
						'max_stock_level' => 1000,
						'target_date' => now(),
						'sold_quantity' => 0,
						'status' => 'slow-movement',
						'current_stock_level' => $item['quantity']
					]);
				}
				
				else {$stockLevel->update(['current_stock_level' => $stockLevel->current_stock_level + $item['quantity']]);}
				
			
				
  				$product_variation = ProductVariation::findOrFail($item['product_variation_id']);
				$product_slug = $product_variation->product?->slug;
  				$product_variation_notifies = $product_variation->notifies()->get();
				   
				if($stockLevel->current_stock_level == 0 && isset($product_variation_notifies)){
					foreach($product_variation_notifies as $notifie){
						$user = User::findOrFail($notifie->id);  
						if($user){
							$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
							foreach($fcm_tokens as $fcm){
								$fcm_token = FcmToken::where([['fcm_token', $fcm],['user_id',$user->id]])->first();
								if($fcm_token->lang == 'en'){
										$this->send_notification($fcm, 
																 'Your favourite product is back',
																 'Your favourite product is back', 
																 'Order', 
																 'flutter_app'); // key source	
									}else{
										$this->send_notification($fcm, 
																 'منتجك قد عاد الى السوق مجدداً',
																 'منتجك قد عاد الى السوق مجدداً',
																 $product_slug, 
																 'flutter_app'); // key source
									}	
								}

							$title = ["en" => 'Your favourite product is back', "ar" => 'منتجك قد عاد الى السوق مجدداً'];

							$body = ["en" => "Your favourite product is back",
									 "ar" => "منتجك قد عاد الى السوق مجدداً"];

							$user->notifications()->create([
								'user_id'=>$user->id,
								'type'=> $product_slug, // 1 is to redirect to the orders page
								'title'=>$title,
								'body'=>$body
							]);	
							$user->notifies()->detach($stockLevel->product_variation_id);
							//$user->notifies()->delete();
						}
					}			   
				}
		    }
			// Assuming $cargo_shipment->cargo_shipment_pv() returns a query builder instance
			// and createMany expects an array of items to be created.
			// Ensure this part is correctly implemented to handle each item.
			// This might need adjustment based on your actual implementation.
			
			$shipment_items = $request->cargo_shipment_items;
			
		foreach($shipment_items as  &$shipment_item){
					

			
			$shipment_item['received'] = $shipment_item['quantity'];
				//	dd($shipment_item);
			
			}
			
			//	dd($shipment_items);
			
			$cargo_shipment->received_date = now();
			$cargo_shipment->save();
		
			$cargo_shipment_pv = $cargo_shipment->cargo_shipment_pv()->createMany($shipment_items);
			
		//	dd($cargo_shipment->cargo_shipment_pv());


			//}

			// After the loop, you might want to commit or rollback transactions if you're using them.
			// Ensure that any changes are committed or rolled back as needed.

			// Return the response outside the loop.
			return response()->success([
				'cargo_shipment' => $cargo_shipment,
				'cargo_shipment_items' => $cargo_shipment_pv
			], 201);
		}
        $cargo_shipment = new CargoShipment();
        $shipment_name = 'TW-' . rand(10000, 99999) . rand(10000, 99999);
        // $cargo_shipment->employee_id = auth('api-employees')->user()->id;
        $cargo_shipment->fill($request->cargo_shipment);
        //$cargo_shipment->employee_id  = $employee_id;
        $cargo_shipment->status = 'open';
        $cargo_shipment->shipment_name = $shipment_name;
        if (isset($request['cargo_shipment']['cargo_request_id'])) {
		//	dd(isset($request['cargo_shipment']['cargo_request_id']));
            $cargo_request = CargoRequest::findOrFail($request['cargo_shipment']['cargo_request_id']);
           // dd($cargo_request);
			if (isset($request['cargo_shipment']['cargo_request_id'])) {
               $hello =  $this->cargo_shipment_pv_service->calculateNewStock($cargo_shipment, 
																	$request->cargo_shipment_items, 
																	$request['cargo_shipment']['from_inventory'],
																	$request['cargo_shipment']['to_inventory']);
				
			
            }
			
            $cargo_shipment->to_inventory = $cargo_request->to_inventory;
			
			

            $cargo_shipment->save();
        } else {
            $this->cargo_shipment_pv_service->calculateNewStock($cargo_shipment, 
																$request->cargo_shipment_items, 
																$request['cargo_shipment']['from_inventory'], 
																$request['cargo_shipment']['to_inventory']);
            $cargo_shipment->to_inventory = $request['cargo_shipment']['to_inventory'];
            $cargo_shipment->save();
            //   $cargo_request = CargoRequest::findOrFail($request->request_id);


            /*  if ($cargo_request) {

                  $cargo_shipment->to_inventory = $cargo_request->to_inventory;

                  $cargo_shipment->save();
              }
              */
        }
        // check if existing open requests

        if (isset($request['cargo_shipment']['cargo_request_id'])) {
            try {
                DB::beginTransaction();
                //  $shipment_name = 'TW-' . rand(10000,99999).rand(10000,99999);
                //$employee_id = 1;
                // $cargo_shipment = CargoShipment::create(array_merge($request->cargo_shipment, ['shipment_name' => $shipment_name,'status' => 'open','employee_id' => $employee_id ]));
			
//dd($request['cargo_shipment']);
                $cargo_shipment_pv = $cargo_shipment->cargo_shipment_pv()->createMany(/*$request->cargo_shipment['cargo_shipment_items'] ??*/ $request['cargo_shipment_items'] );
                $request_to_ship = CargoRequest::findOrFail($request->cargo_shipment['cargo_request_id'])->load('cargo_request_pv');
                $request_to_ship->update(['status' => 'pending', 'request_status_id' => CargoRequestStatus::PENDING]);
                /*   if ($cargo_shipment->from_inventory == 'first_point') {
                      /* $this->notified_employees->each(function ($item) use ($cargo_shipment) {
                           // Assuming $item->fcm_token is an object with an fcm_token property
                           $this->send_notification($item->fcm_token->fcm_token, 'استلام شحنة منتج جديد', 'تم استلام شحنة منتج جديد في المستودع ' . $cargo_shipment->to_inventory, null);

                           // Assuming $user is defined and accessible in this scope
                           $item->notifications()->create([
                               'employee_id' => $item->id,
                               'type' => 1, // 1 is to redirect to the orders page
                               'title' => 'استلام شحنة منتج جديد',
                               'body' => 'نتمنى أن تكون العملية قد نالت استحسانك'
                           ]);
                       });*/
                /*    } else {

                        $this->notified_employees->each(function ($item) use ($cargo_shipment) {
                            // Assuming $item->fcm_token is an object with an fcm_token property
                            $this->send_notification($item->fcm_token->fcm_token, 'استلام شحنة منتج', 'تم استلام شحنة منتج في المستودع ' . $cargo_shipment->to_inventory, null);

                            // Assuming $user is defined and accessible in this scope
                            $item->notifications()->create([
                                'employee_id' => $item->id,
                                'type' => 1, // 1 is to redirect to the orders page
                                'title' => 'استلام شحنة منتج',
                                'body' => 'نتمنى أن تكون العملية قد نالت استحسانك'
                            ]);
                        });
                    }
    */
                // $this->send_notification($employee->fcm_token->fcm_token, $request->cargo_shipment_);
                /* if( gettype($response) == 'array'){
                    return response()->error($response,400);   
                }
                */
                // $request_to_ship->cargo_requests_pv->update(['requested_from_manager' => ])
                DB::commit();
                return response()->success(['cargo_shipment' => $cargo_shipment, 
											'cargo_shipment_product_variations' => $cargo_shipment_pv, 
											'cargo_request' => $request_to_ship, 
											'message' => 'Shipment has been sent successfully'], 
										   201);
            } catch (\Exception $e) {
                DB::rollBack();
				
                if (!$e instanceof ModelNotFoundException || !$e instanceof OutOfStockException) {
                    return response()->error(['message' => $e->getMessage(),$e->getLine()], 400);
                } else
                    throw $e;
            }
        }
        //  $open_request = CargoRequest::where('request_status_id', 1)->get();
        else {
            //   $shipment_name = 'TW-' . rand(10000,99999).rand(10000,99999);
            // $cargo_shipment = CargoShipment::create(array_merge($request->cargo_shipment, ['to_inventory' => $request->to_inventory,'shipment_name' => $shipment_name,'status' => 'open','employee_id' => $employee_id ]));
			
	
            $cargo_shipment_pv = $cargo_shipment->cargo_shipment_pv()->createMany($request->cargo_shipment_items);
            // $request_to_ship = CargoRequest::findOrFail($request->cargo_shipment['cargo_request_id'])->load('cargo_request_pv');
            //  $request_to_ship->update(['status' => 'pending', 'request_status_id' => CargoRequestStatus::PENDING]);
            /* $this->notified_employees->each(function ($item) use ($cargo_shipment) {
                 $this->send_notification($item->fcm_token->fcm_token, 'استلام شحنة منتج', 'تم استلام شحنة منتج جديد في المستودع ' . $cargo_shipment->to_inventory, null);
                 $item->notifications()->create([
                     'employee_id' => $item->id,
                     'type' => 1, // 1 is to redirect to the orders page
                     'title' => 'استلام شحنة منتج رداً على طلب سابق',
                     'body' => 'نتمنى أن تكون العملية قد نالت استحسانك'
                 ]);
             });*/
            return response()->success(['cargo_shipment' => $cargo_shipment, 
										'cargo_shipment_product_variations' => $cargo_shipment_pv, 
										'message' => 'Shipment has been sent successfully'], 
									   201);
        }
    }



    public function shiped(Request $request)
    {
        $cargo_shipment = CargoShipment::with(['cargo_request.cargo_requests_pv', 'cargo_shipment_pv'])
            ->findOrFail($request->cargo_shipment_id);

        if (!$cargo_shipment->cargo_request) {
            $cargo_shipment->update(['status' => 'closed','received_date' => now()]);
            $cargo_shipment->cargo_shipment_pv->each(function ($item) use ($request,$cargo_shipment) {

                $received_item = collect($request->items_received)->where('product_variation_id', $item->product_variation_id)->first();
                if ($received_item) {
                    $item->update(['received' => $received_item['quantity']]);
					//	dd(	$cargo_shipment);
					$stock_level = $item->product_variation()->first()->stock_levels()->where('inventory_id',$cargo_shipment->from_inventory)->first();
					
					$stock_level->update([
					'on_hold' => $stock_level->on_hold - $received_item['quantity'],
						'current_stock_level' => $received_item['quantity']
					
					]);  
                }
							

				
            });
			   return response()->success($cargo_shipment->cargo_shipment_pv, 200);
			
        } else {
            $cargo_request = $cargo_shipment->cargo_request;
            $cargo_shipment->update(['status' => 'closed','received_date' => now()]);
            $cargo_request->update([
                'status' => 'closed',
                'request_status_id' => CargoRequestStatus::CLOSED,
                'ship_date' => now()
            ]);



          $cargo_request->cargo_requests_pv->each(function ($item) use ($request) {
    $received_item = collect($request->items_received)
        ->where('product_variation_id', $item->product_variation_id)
        ->first();

    if ($received_item) {
        $item->update(['requested_from_manager' => $received_item['quantity']]);
		     

	$stock_level = $item->product_variation()->first()->stock_levels()->where('inventory_id',$cargo_shipment->from_inventory)->first();
					
					$stock_level->update([
					'on_hold' => $stock_level->on_hold - $received_item['quantity'],
						'current_stock_level' => $received_item['quantity']
					
					]);  
    }
});

        return response()->success($cargo_shipment->cargo_shipment_pv, 200);
    }
	}
}