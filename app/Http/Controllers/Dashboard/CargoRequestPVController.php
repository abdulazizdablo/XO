<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CargoRequestService;
use App\Utils\PaginateCollection;
use App\Enums\Roles;
use App\Http\Requests\Shipments\SendCargoRequest;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;
use App\Services\CargoRequestPVService;
use Exception;

class CargoRequestPVController extends Controller
{

    public function __construct(protected CargoRequestService $cargo_request_service, protected CargoRequestPVService $cargoRequestPVService, protected PaginateCollection $paginateCollection, protected InventoryService $inventoryService)
    {
    }

    public function send(SendCargoRequest $request)//si
    {
        $employee = auth('api-employees')->user();

        if (!$employee) {

            return response()->error(['message' => 'Unauthinticated'], 400);
        }

        try {
            DB::beginTransaction();

			if($employee->hasRole(Roles::OPERATION_MANAGER) || $employee->hasRole(Roles::MAIN_ADMIN) || $employee->hasRole(Roles::WAREHOUSE_ADMIN))				
			{
			   $cargo_request = $this->cargo_request_service->sendRequest($request->cargo_request, $request->cargo_request['inventory_id'], $employee->id);
			}
			
			else {
            $cargo_request = $this->cargo_request_service->sendRequest($request->cargo_request, $employee->inventory_id, $employee->id);
			}

            $cargo_request_pv = $this->cargoRequestPVService->sendMany($cargo_request, $request->cargo_request_items);
            
            DB::commit();

            return response()->success(['request' => $cargo_request, 'request_product_variations' => $cargo_request_pv, 'message' => 'Request has been sent successfully'], 201);
        } catch (Exception $e) {
            DB::rollback();
            return response()->error(['message'=>$e->getMessage()],400);
        }
    }
}
