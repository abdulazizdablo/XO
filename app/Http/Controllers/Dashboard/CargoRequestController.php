<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CargoRequest;
use App\Services\CargoRequestService;
use App\Enums\CargoRequestStatus;
use App\Models\CargoShipment;
use App\Models\ProductVariation;
use App\Utils\PaginateCollection;
use App\Enums\Roles;
use App\Http\Requests\FilterRequest;
use App\Services\InventoryService;
use App\Models\Inventory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

class CargoRequestController extends Controller
{

    public function __construct(protected CargoRequestService $cargo_request_service, protected PaginateCollection $paginateCollection, protected InventoryService $inventoryService) {}

    public function requestCount(Request $request) //si
    {
        $validatedData = $request->validate([
            'dateScope' => ['nullable', 'string', 'in:Today,last_week,last_month,last_quarter,last_year'],
        ]);
        $dateScope = $validatedData['dateScope'] ?? 'Today';
        // $dateScope = request('dateScope');
        $employee = auth('api-employees')->user();
        $data = [];

        if ($employee->hasRole(Roles::OPERATION_MANAGER) || $employee->hasRole(Roles::WAREHOUSE_ADMIN) || $employee->hasRole(Roles::MAIN_ADMIN)) {
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

            $logistics_count = [
                'closed_requests_count' => $closed,
                'open_requests_count' => $open,
                'arrived_requests_count' => $shipments,
                'expected_delivered_requests_count' => $expected
            ];

            return response()->success($logistics_count, 200);
        }

        if ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
            $from_date = null;
            $to_date = null;

            if ($dateScope == null) {
                $dateScope = 'Today';
            }

            $shipment = CargoShipment::all();
            $modelName = \App\Models\CargoShipment::class;
            $closed_requests_count = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)
                ->where('to_inventory', $employee->inventory_id)
                ->where('status', 'closed')
                ->count();

            $open_requests_count = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)
                ->where('to_inventory', $employee->inventory_id)
                ->where('status', 'open')
                ->count();

            $expected_delivered_requests_count = CargoShipment::scopeDateRange($shipment, $modelName, $dateScope, $from_date, $to_date)
                ->where('to_inventory', $employee->inventory_id)
                ->where('status', 'closed')
                ->whereHas('cargo_shipment_pv', function ($query) {
                    $query->whereColumn('received', '!=', 'quantity');
                })->count();
            $arrived_requests_count = $closed_requests_count + $expected_delivered_requests_count;
            $data = [
                'closed_requests_count' => $closed_requests_count,
                'open_requests_count' => $open_requests_count,
                'arrived_requests_count' => $arrived_requests_count,
                'expected_delivered_requests_count' => $expected_delivered_requests_count,
            ];

            return response()->success($data, 200);
        } else {
            $closed_requests_count = CargoShipment::where('status', 'closed')->count();
            $open_requests_count = CargoShipment::where('status', 'open')->count();
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

    public function applyFilters($query, array $filters, $route_name = null) //si
    {
        $appliedFilters = [];
        foreach ($filters as $attribute => $value) {
            $segments = explode('_', $attribute);
            if (count($segments) >= 2) {
                $firstTwoSegments = $segments[0] . '_' . $segments[1];
            } else {
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

    public function getLogisticsCargoRequests(FilterRequest $request) //si
    {
        $filter_data = $request->only(['date_send_min', 'date_send_max', 'status', 'search']);
        $warehouse_manager = auth('api-employees')->user();
        // $routeName = Route::currentRouteName();
        if ($warehouse_manager->hasRole(Roles::OPERATION_MANAGER)) {

            $cargo_requests = CargoRequest::with('cargo_request_pv')->latest();

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
            $cargo_requests = $this->paginateCollection::paginate($cargo_requests, 10);
            return response()->success($cargo_requests->toArray(), 200);
        }

        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER) || $warehouse_manager->hasRole(Roles::WAREHOUSE_ADMIN) || $warehouse_manager->hasRole(Roles::OPERATION_MANAGER)) {

            $cargo_requests = CargoRequest::where('to_inventory', $warehouse_manager->inventory_id)->latest();

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

            $cargo_requests = $this->paginateCollection::paginate($cargo_requests, 10);
            return response()->success($cargo_requests->toArray(), 200);
        } else {

            return response()->error('Unauthorized', 401);
        }
    }

    public function getLogisticsMyCargoShipment(FilterRequest $request) //si
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
            $shipments = CargoShipment::with('inventory', 'cargo_request.inventory', 'cargo_request.cargo_requests_pv')->where('to_inventory', $inventory_id)->latest();

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

    public function getLogisticsAssignedCargoShipment(FilterRequest $request) //si
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

        if (!auth('api-employees')->check()) {
            return response()->error('Unauthorized', 403);
        }
        $warehouse_manager = auth('api-employees')->user();

        if ($warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER)) {

            $shipments = CargoShipment::with('inventory', 'cargo_request.inventory', 'cargo_request.cargo_requests_pv', 'cargo_shipment_pv')->latest()
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

            $shipments = $this->paginateCollection::paginate($shipments, 10, 20);
        }
        return response()->success($shipments, 200);
    }

    public function importProduct(FilterRequest $request) //si
    {
        $warehouse_manager = auth('api-employees')->user();
        if($request->inventory_id != 'first_point'){
            $validatedData = $request->validate([
                'inventory_id' => ['required', 'integer', 'exists:inventories,id'],
            ]);
            $inventory_id = $validatedData['inventory_id'];
        }else{
            $inventory_id = 'first_point';  
        }

        $filter_data = $request->only(['status', 'is_new', 'date_min', 'date_max', 'sku_code', 'product_name', 'price_min', 'price_max']);
        $query = ProductVariation::select('id', 'product_id', 'sku_code', 'color_id')
            ->with([
                'product.pricing',
                'product',
                'stock_levels',
                'photos'
            ]);

        if (isset($filter_data['status'])) {
            $query->whereHas('product', function ($query) use ($filter_data) {
                $query->where('available', $filter_data['status']);
            });
        }

        if (isset($filter_data['is_new'])) {
			if($filter_data['is_new'] == 1){
				$query->whereHas('product', function ($query) use ($filter_data) {
					$query->where('isNew', $filter_data['is_new']);
				});
			}      
        }

        if (isset($filter_data['date_min']) && isset($filter_data['date_max'])) {
            $query->whereBetween('created_at', [$filter_data['date_min'], $filter_data['date_max']]);
        } elseif (isset($filter_data['date_min'])) {
            $query->where('created_at', '>=', $filter_data['date_min']);
        } elseif (isset($filter_data['date_max'])) {
            $query->where('created_at', '<=', $filter_data['date_max']);
        }

        if (isset($filter_data['sku_code'])) {
            $query->where('sku_code', 'like', '%' . $filter_data['sku_code'] . '%');
        }

        if (isset($filter_data['product_name'])) {
            $query->whereHas('product', function ($q) use ($filter_data) {
                $q->where('name->en', 'like', '%' . $filter_data['product_name'] . '%')
                    ->orWhere('name->ar', 'like', '%' . $filter_data['product_name'] . '%');
            });
        }

        if (isset($filter_data['price_min']) || isset($filter_data['price_max'])) {
            $query->whereHas('pricing', function ($q) use ($filter_data) {
                if (isset($filter_data['price_min'])) {
                    $q->where('price', '>=', $filter_data['price_min']);
                }
                if (isset($filter_data['price_max'])) {
                    $q->where('price', '<=', $filter_data['price_max']);
                }
            });
        }
        if (
            $warehouse_manager->hasRole(Roles::WAREHOUSE_MANAGER) ||
            $warehouse_manager->hasRole(Roles::OPERATION_MANAGER)
        ) {
            if ($inventory_id == 'first_point') {

                $products_first_point_shipped = $query->get();

                $products_defined = $products_first_point_shipped->map(function ($item) {
                    return [
                        'product_variation_id' => $item->id,
                        'name' => $item->product?->name,
                        'sku_code' => $item->sku_code,
                        'stock' => $item->stock_levels()->exists()
                            ? (int) $item->stock_levels->sum('current_stock_level')
                            : 0,
                        'price' => $item->product?->pricing->value,
                        'photo' => $item->product?->photosByColorId((array)$item->color_id)->first(),
                    ];
                });

                return response()->success([
                    'inventory_name' => 'First Point Warehouse',
                    'products' => $products_defined
                ], 200);
            }

            $inventory = Inventory::findOrFail($inventory_id);

            if (isset($inventory_id)) {
                $inventory = Inventory::findOrFail($inventory_id)->load('stock_levels');
            }

            $stock_levels = $inventory->stock_levels;

            $inventory_stock = $query->whereIn('id', $stock_levels->pluck('product_variation_id'))->get();

            $inventory_data = $inventory_stock
                ->filter(function ($item) use ($inventory_id, $inventory) {
                    return $item->stock_levels()->where('inventory_id', $inventory_id)->first()->current_stock_level != 0;
                })
                ->map(function ($item) use ($inventory_id, $inventory) {
                    $collection = collect([
                        'product_variation_id' => $item->id,
                        'name' => $item->product()->first()->name,
                        'sku_code' => $item->sku_code,
                        'stock' => $item->stock_levels()->where('inventory_id', $inventory_id)->first()->current_stock_level,
                        'price' => $item->product()->first()->pricing->value,
                        'status' => $item->stock_levels->first()->status,
                        'photo' => $item->product()->first()->photos()->where('color_id', $item->color_id)->first(),
                    ]);

                    if ($item->stock_levels->where('inventory_id', $inventory_id)
                        ->first()->current_stock_level <= 10
                    ) {
                        $collection->put('raise', true);
                    } else {
                        $collection->put('raise', false);
                    }

                    return $collection;
                })->values();

            return response()->success(['inventory_name' => $inventory->name, 'products' => $inventory_data->values()], 200);
        } else {

            return response()->error('Unauthorized', 401);
        }
    }
}
