<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockLevel;
use App\Services\StockLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class StockLevelController extends Controller
{


    public function __construct(
        protected   StockLevelService $stockLevelService
    ) {
    }

    public function getProductsStock(Request $request)//si
    {
		$employee = auth('api-employees')->user();
		$type = request('type');
		if (!$employee) {
            return response()->error(['message' => 'Unauthinticated'], 400);
        }
	
		if ($employee->has_role('main_admin') || $employee->has_role('operation_manager')){
			$inventory_id = request('inventory_id');
			if ($inventory_id == 0) {$inventory_id = null;}
		}else{
			$inventory_id = $employee->inventory_id;
		}

        $filter_data = $request->only(['date_min', 'date_max', 'stock_min', 'stock_max', 'status' ,'price_min', 'price_max','search']);
        $sort_data = $request->only(['sort_key', 'sort_value']);

        $stock_levels = $this->stockLevelService->getInventoryProducts($sort_data ,$filter_data, $inventory_id, $type);

        return response()->success(
            $stock_levels
            , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockLevel $stock_level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)//si
    {
        try {
            $stock_level_id = request('stock_level_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'sometimes',
                    'min_stock_level' => 'sometimes|integer',
                    'current_stock_level' => 'sometimes|integer',
                    'max_stock_level' => 'sometimes|integer',
                    'target_date' => 'sometimes|max:255',
                    'sold_quantity' => 'sometimes|integer',
                    'status' => 'sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                   422
                );
            }

            $stock_level_data = $validate->validated();

            $stock_level = $this->stockLevelService->updateStockLevel($stock_level_data, $stock_level_id);

            return response()->success(
                $stock_level,
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

}
