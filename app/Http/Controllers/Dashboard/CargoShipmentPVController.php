<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Inventories;
use App\Http\Controllers\Controller;
use App\Models\CargoRequest;
use App\Models\CargoShipment;
use App\Models\FcmToken;
use App\Models\StockLevel;
use App\Services\CargoRequestPVService;
use App\Services\CargoShipmentPVService;
use App\Enums\CargoRequestStatus;
use App\Enums\Roles;
use App\Http\Requests\Shipments\SendShipmentRequest;
use App\Http\Requests\Shipments\ShipmentArrivedRequest;
use App\Traits\FirebaseNotificationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProductVariation;
use App\Models\Inventory;
use App\Models\User;

class CargoShipmentPVController extends Controller
{
    use FirebaseNotificationTrait;

    //  protected $notified_employees;
    public function __construct(
        protected CargoShipmentPVService $cargo_shipment_pv_service,
        protected CargoRequestPVService $cargo_request_pv_service
    ) {}


    public function send(SendShipmentRequest $request) //si
    {
        $employee = auth('api-employees')->user();

        if (!$employee->hasRole(Roles::OPERATION_MANAGER)) {
            return response()->error('Permission denied', 401);
        }

        if ($request->cargo_shipment['to_inventory'] == $request->cargo_shipment['from_inventory']) {
            return response()->error('inventories are the same', 400);
        }

        $cargo_shipment = new CargoShipment();

        if ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
            $cargo_shipment->from_inventory = $employee->inventory_id;
        }

        $shipment_name = 'TW-' . random_int(10000, 99999) . random_int(10000, 99999);
        $inventory = Inventory::findOrFail($request->cargo_shipment['to_inventory']);
        $cargo_shipment->to_inventory = $inventory->id;

        if (isset($request->cargo_shipment['from_inventory']) && $request->cargo_shipment['from_inventory'] == 'first_point') {
            $cargo_shipment->to_inventory =  Inventories::ALEPPO;
            $cargo_shipment->from_inventory = null;
            $cargo_shipment->status = 'closed';
            $cargo_shipment->shipment_name = $shipment_name;
            $cargo_shipment->save();
            foreach ($request->cargo_shipment_items as $key => $item) {
                $product_variation = [];
                $stockLevel = StockLevel::where('inventory_id', Inventories::ALEPPO)
                    ->where('product_variation_id', $item['product_variation_id'])
                    ->first();
                if (!$stockLevel) {
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
                } else {
                    $stockLevel->update(['current_stock_level' => $stockLevel->current_stock_level + $item['quantity']]);
                }
                $product_variation = ProductVariation::findOrFail($item['product_variation_id']);
                $product_slug = $product_variation->product?->slug;
                $product_variation_notifies = $product_variation->notifies()->get();

                if ($stockLevel->current_stock_level == 0 && isset($product_variation_notifies)) {
                    foreach ($product_variation_notifies as $notifie) {
                        $user = User::findOrFail($notifie->id);
                        if ($user) {
                            $fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
                            foreach ($fcm_tokens as $fcm) {
                                $fcm_token = FcmToken::where([['fcm_token', $fcm], ['user_id', $user->id]])->first();
                                if ($fcm_token->lang == 'en') {
                                    $this->send_notification(
                                        $fcm,
                                        'Your favourite product is back',
                                        'Your favourite product is back',
                                        'Order',
                                        'flutter_app'
                                    );
                                } else {
                                    $this->send_notification(
                                        $fcm,
                                        'منتجك قد عاد الى السوق مجدداً',
                                        'منتجك قد عاد الى السوق مجدداً',
                                        $product_slug,
                                        'flutter_app'
                                    );
                                }
                            }

                            $title = ["en" => 'Your favourite product is back', "ar" => 'منتجك قد عاد الى السوق مجدداً'];

                            $body = [
                                "en" => "Your favourite product is back",
                                "ar" => "منتجك قد عاد الى السوق مجدداً"
                            ];

                            $user->notifications()->create([
                                'user_id' => $user->id,
                                'type' => $product_slug,
                                'title' => $title,
                                'body' => $body
                            ]);
                            $user->notifies()->detach($stockLevel->product_variation_id);
                        }
                    }
                }

                if (isset($request['cargo_shipment']['cargo_request_id'])) {
                    $cargo_request = CargoRequest::findOrFail($request['cargo_shipment']['cargo_request_id']);
                    $cargo_request->update([
                        'request_status_id' => CargoRequestStatus::CLOSED,
                        'status' => 'closed'
                    ]);
                }
            }

            $shipment_items = $request->cargo_shipment_items;

            foreach ($shipment_items as  &$shipment_item) {
                $shipment_item['received'] = $shipment_item['quantity'];
            }

            $cargo_shipment->received_date = now();
            $cargo_shipment->save();

            $cargo_shipment_pv = $cargo_shipment->cargo_shipment_pv()->createMany($shipment_items);

            return response()->success([
                'cargo_shipment' => $cargo_shipment,
                'cargo_shipment_items' => $cargo_shipment_pv
            ], 201);
        }

        $cargo_shipment = new CargoShipment();
        $shipment_name = 'TW-' . mt_rand(10000, 99999) . mt_rand(10000, 99999);
        $cargo_shipment->fill($request->cargo_shipment);
        $cargo_shipment->status = 'open';
        $cargo_shipment->shipment_name = $shipment_name;
        if (isset($request['cargo_shipment']['cargo_request_id'])) {
            $cargo_request = CargoRequest::findOrFail($request['cargo_shipment']['cargo_request_id']);
            $this->cargo_shipment_pv_service->calculateNewStock(
                $cargo_shipment,
                $request->cargo_shipment_items,
                $request['cargo_shipment']['from_inventory'],
                $request['cargo_shipment']['to_inventory']
            );
            $cargo_shipment->to_inventory = $cargo_request->to_inventory;
            $cargo_shipment->save();
        } else {
            $this->cargo_shipment_pv_service->calculateNewStock(
                $cargo_shipment,
                $request->cargo_shipment_items,
                $request['cargo_shipment']['from_inventory'],
                $request['cargo_shipment']['to_inventory']
            );
            $cargo_shipment->to_inventory = $request['cargo_shipment']['to_inventory'];
            $cargo_shipment->save();
        }

        if (isset($request['cargo_shipment']['cargo_request_id'])) {
            try {
                DB::beginTransaction();
                $cargo_shipment_pv = $cargo_shipment->cargo_shipment_pv()->createMany($request['cargo_shipment_items']);
                $request_to_ship = CargoRequest::findOrFail($request->cargo_shipment['cargo_request_id'])->load('cargo_request_pv');
                $request_to_ship->update(['status' => 'pending', 'request_status_id' => CargoRequestStatus::PENDING]);

                DB::commit();
                return response()->success(
                    [
                        'cargo_shipment' => $cargo_shipment,
                        'cargo_shipment_product_variations' => $cargo_shipment_pv,
                        'cargo_request' => $request_to_ship,
                        'message' => 'Shipment has been sent successfully'
                    ],
                    201
                );
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->error(['message' => $e->getMessage()], 400);
            }
        } else {
            $cargo_shipment_pv = $cargo_shipment->cargo_shipment_pv()->createMany($request->cargo_shipment_items);

            return response()->success(
                [
                    'cargo_shipment' => $cargo_shipment,
                    'cargo_shipment_product_variations' => $cargo_shipment_pv,
                    'message' => 'Shipment has been sent successfully'
                ],
                201
            );
        }
    }


    public function shiped(ShipmentArrivedRequest $request) //si
    {
        $employee = auth('api-employees')->user();

        if (!$employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
            return response()->error('Permission denied', 401);
        }

        $cargo_shipment = CargoShipment::with(['cargo_request.cargo_requests_pv', 'cargo_shipment_pv'])
            ->findOrFail($request->cargo_shipment_id);

        if($cargo_shipment->to_inventory != $employee->inventory_id){
            return response()->error('Permission denied', 401);    
        }
    
        if($cargo_shipment->status == 'closed'){
            return response()->error(['message' => 'Shipment already closed'],400);
        }

        if (!$cargo_shipment->cargo_request) {
            $cargo_shipment->update(['status' => 'closed', 'received_date' => now()]);
            $cargo_shipment->cargo_shipment_pv->each(function ($item) use ($request, $cargo_shipment) {

                $received_item = collect($request->items_received)->where('product_variation_id', $item->product_variation_id)->first();
                
                if ($received_item) {
                    $item->update(['received' => $received_item['quantity']]);
                    $stock_level = $item->product_variation()->first()->stock_levels()->where('inventory_id', $cargo_shipment->from_inventory)->first();

                    $stock_level->update([
                        'shipment_hold' => $stock_level->shipment_hold - $received_item['quantity'],
                    ]);
                    $receiver_stock = $item->product_variation()->first()->stock_levels()->where('inventory_id', $cargo_shipment->to_inventory)->first();
                }

                if (!$receiver_stock) {
                    $receiver_stock = StockLevel::create([
                        'product_variation_id' => $item['product_variation_id'],
                        'inventory_id' => $cargo_shipment->to_inventory,
                        'name' => 'Initial Shipment',
                        'min_stock_level' => 3,
                        'max_stock_level' => 1000,
                        'target_date' => now(),
                        'sold_quantity' => 0,
                        'status' => 'slow-movement',
                        'current_stock_level' => $received_item['quantity']
                    ]);
                } else {
                    $receiver_stock->update([
                        'current_stock_level' => $receiver_stock->current_stock_level + $received_item['quantity']
                    ]);
                }
            });
            return response()->success($cargo_shipment->cargo_shipment_pv, 200);
        } else {
            $cargo_request = $cargo_shipment->cargo_request;
            $cargo_shipment->update(['status' => 'closed', 'received_date' => now()]);
            $cargo_request->update([
                'status' => 'closed',
                'request_status_id' => CargoRequestStatus::CLOSED,
                'ship_date' => now()
            ]);

            $cargo_request->cargo_requests_pv->each(function ($item) use ($request, $cargo_shipment) {
                $received_item = collect($request->items_received)
                    ->where('product_variation_id', $item->product_variation_id)
                    ->first();

                if ($received_item) {
                    $item->update(['requested_from_manager' => $received_item['quantity']]);


                    $stock_level = $item->product_variation()->first()->stock_levels()->where('inventory_id', $cargo_shipment->from_inventory)->first();

                    $stock_level->update([
                        'shipment_hold' => $stock_level->shipment_hold - $received_item['quantity'],
                        'current_stock_level' => $received_item['quantity']

                    ]);
                }
            });

            return response()->success($cargo_shipment->cargo_shipment_pv, 200);
        }
    }
}
