<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CargoRequest;
use App\Models\CargoShipment;
use Illuminate\Support\Str;
use App\Enums\CargoRequestStatus;
use Illuminate\Http\Request;
use App\Utils\PaginateCollection;
use App\Services\InventoryService;
use App\Enums\Roles;
use App\Http\Requests\FilterRequest;
use App\Models\StockLevel;
use App\Models\Photo;

class CargoShipmentController extends Controller
{
    public function __construct(protected PaginateCollection $paginate_collection, protected InventoryService $inventoryService) {}

    public function confirmShipment(Request $request) //si
    {
        $validatedData = $request->validate([
            'cargo_shipment_id' => ['required', 'integer', 'exists:cargo_shipments,id'],
            'sender_packages' => ['required' , 'integer', 'min:1']

        ]);

        $employee = auth('api-employees')->user();
        
        if (!$employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
            return response()->error('Permission denied', 401);
        }

        $cargo_shipment_id = $validatedData['cargo_shipment_id'];
        $sender_packages = $validatedData['sender_packages'];
        
        $cargo_shipment = CargoShipment::findOrFail($cargo_shipment_id)->load('cargo_shipment_pv');

        if($cargo_shipment->from_inventory != $employee->inventory_id){
            return response()->error('Permission denied', 401);    
        }

        if ($cargo_shipment->status == 'open') {
            $cargo_shipment->update(['status' => 'pending', 'sender_packages' => $sender_packages]);
            foreach ($cargo_shipment->cargo_shipment_pv as $item) {
                $stock_level = StockLevel::where('inventory_id', $cargo_shipment->from_inventory)->where('product_variation_id', $item['product_variation_id'])->first();
                $stock_level->update(['shipment_hold' => $item->quantity]);
            }
            return response()->success(['message' => 'Shipment has been sent successfully', 'cargo_shipment' => $cargo_shipment], 200);
        }else{
            return response()->error(['message' => 'Shipment already confirmed'], 400);    
        }
    }

    public function shipmentDetailsItems(Request $request) //si
    {
        $validatedData = $request->validate([
            'cargo_shipment_id' => ['required', 'integer', 'exists:cargo_shipments,id'],
        ]);
        $cargo_shipment_id = $validatedData['cargo_shipment_id'];
        $shipment = CargoShipment::findOrFail($cargo_shipment_id)->load('cargo_request', 'cargo_request.cargo_requests_pv', 'inventory');
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

            $cargo_request_shipment_items->each(function ($item) use ($send_items, $from_inventory, $to_inventory, $cargo_request_shipment, $shipment) {
                $product = $item->product_variation()->first()->product()->first();
				$photo = Photo::where([['color_id',$item->product_variation->color_id],['product_id',$product->id],['main_photo',1]])->first();
                //$photo = $item->product_variation->photos()->where('photos.color_id', $item->product_variation->color_id)->first();
                $item->product_name = $product->name;
                $item->sku_code = $item->product_variation->sku_code;
                $item->desired_items = $cargo_request_shipment?->cargo_requests_pv->where('product_variation_id', $item->product_variation_id)->first()->requested_from_inventory ?? null;
                $item->items_be_sent = $item->quantity;
                $item->items_received = $cargo_request_shipment?->cargo_requests_pv->where('product_variation_id', $item->product_variation_id)->first()->requested_from_manager ?? $shipment->cargo_shipment_pv()->where('product_variation_id', $item->product_variation_id)->first()->received;
                $item->source_inventory = $from_inventory;
                $item->destination_inventory = $to_inventory;
                $item->photos = $photo;
            });
        });

        $cargo_request_shipment_items = $this->paginate_collection::paginate($cargo_request_shipment_items, 10, 20);

        return response()->success([

            'shipment_items' => $cargo_request_shipment_items,
            'status' => $shipment->status

        ], 200);
    }

    public function requestDetails(Request $request) //si
    {
        $validatedData = $request->validate([
            'cargo_request_id' => ['required', 'integer', 'exists:cargo_requests,id'],
        ]);
        $cargo_request_id = $validatedData['cargo_request_id'];
        $cargo_request = CargoRequest::findOrFail($cargo_request_id)->load('inventory');
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

    public function requestAndShipmentDetails(Request $request) //si
    {
        $validatedData = $request->validate([
            'cargo_shipment_id' => ['required', 'integer', 'exists:cargo_shipments,id'],
        ]);
        $cargo_shipment_id = $validatedData['cargo_shipment_id'];
        $cargo_shipment = CargoShipment::findOrFail($cargo_shipment_id)->load('cargo_request', 'cargo_request.inventory', 'inventory');

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

    public function requestDetailsItems(Request $request) //si
    {
        $validatedData = $request->validate([
            'cargo_request_id' => ['required', 'integer', 'exists:cargo_requests,id'],
        ]);
        $cargo_request_id = $validatedData['cargo_request_id'];
        $cargo_request = CargoRequest::findOrFail($cargo_request_id)->load('cargo_requests_pv', 'inventory');
        $cargo_request_items = $cargo_request->cargo_requests_pv->values();
        $cargo_request_items->each(function ($item) use ($cargo_request) {
            $product =  $item->product_variation()->first()->product()->first();
            $photo = $item->product_variation->photos()->where('photos.color_id', $item->product_variation->color_id)->first();
            $item->product_name = $product->name;
            $item->sku_code = $item->product_variation->sku_code;
            $item->desired_items = $item->requested_from_inventory;
            $item->to_inventory = $cargo_request->inventory->name;
            $item->photo = $photo;
        });

        $cargo_request_items = $this->paginate_collection::paginate($cargo_request_items, 10);

        return response()->success([
            'request_items' => $cargo_request_items
        ], 200);
    }

    public function getAllShipments(FilterRequest $request) //si
    {
        $filter_data = $request->only(['date_created_min', 'date_created_max', 'date_send_min', 'date_send_max', 'date_received_min', 'date_received_max', 'status', 'search']);
        $employee = auth('api-employees')->user();

        if ($employee->hasRole(Roles::OPERATION_MANAGER)|| $employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::MAIN_ADMIN)) {
            $shipments = CargoShipment::query();

            if (!empty($filter_data)) {
                $shipments = $this->applyFilters($shipments, $filter_data);
            }

            if ($shipments->count() == 0) {
                return response()->success([], 204);
            }

            $shipments = $shipments->latest()->get();
            $shipments->each(function ($item) {
                $item->source_inventory = $item->inventory()->first()->name ?? 'First Point Inventory';
                $item->destination_inventory = $item->to_inventory()->first()->name ?? null;
                $item->expected = (int)$item->cargo_shipment_pv()->sum('quantity');
                $item->received = $item->cargo_shipment_pv()->sum('received');
            });

            $shipments = $this->paginate_collection::paginate($shipments, 10);
            return response()->success($shipments, 200);
        } else
            return response()->error(['message' => 'Unauthorized'], 403);
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
        return $query->where('shipment_name', 'LIKE', '%' . $search . '%');
    }

}
