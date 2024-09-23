<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockLevel;
use App\Services\StockLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class StockLevelController extends Controller
{


    public function __construct(
        protected   StockLevelService $stockLevelService
    ) {
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $products = $this->stockLevelService->getAllStockLevels();

            return response()->success([
                $products
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function getProductsStock(Request $request)
    {
		$employee = auth('api-employees')->user();
		
		if (!$employee) {
            return response()->error(['message' => 'Unauthinticated'], 400);
        }
		
		if ($employee->has_role('main_admin')){
			$inventory_id = request('inventory_id');
			if ($inventory_id == 0) {$inventory_id = null;}
		}else{
			$inventory_id = $employee->inventory_id;
		}

		$key = request('key');
		
		//$inventory_id = auth()->guard('api-employees')->user()->inventory_id;
        // return $user;

        $filter_data = $request->only(['date_min', 'date_max', 'stock_min', 'stock_max', 'status' ,'price_min', 'price_max','search']);
        $sort_data = $request->only(['sort_key', 'sort_value']);

        $stock_levels = $this->stockLevelService->getInventoryProducts($sort_data ,$filter_data, $inventory_id);

        return response()->success(
            $stock_levels
            , Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $sku_id = request('sku_id');
        $location_id = request('location_id');
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'stock.name' => 'required|string|max:255',
                    'stock.min_stock_level' => 'required',
                    'stock.max_stock_level' => 'required',
                    'stock.safety_stock_level' => 'required',
                    'stock.target_date' => 'required',
                    'stock.sold_quantity' => 'required',
                    'stock.status' => 'required',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                    422
                );
            }

            $validated_data = $validate->validated();
            $stock_level_data = $validated_data['stock'];

            $stock_level = $this->stockLevelService->createStockLevel($stock_level_data, $sku_id, $location_id);

            return response()->success(
                [
                    'message' => 'Stock Level Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockLevel $stock_level
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $stock_level_id = request('stockLevel_id');
            $stock_level = $this->stockLevelService->getStockLevel($stock_level_id);

            return response()->success(
                [
                    'stock_level' => $stock_level
                ],
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockLevel $stock_level
     * @return \Illuminate\Http\Response
     */
    public function edit(StockLevel $stock_level)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockLevel $stock_level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockLevel $stock_level
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $stock_level_id = request('stockLevel_id');
            $stock_levelService = $this->stockLevelService->delete($stock_level_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockLevel $stock_level
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $stock_level_id = request('stockLevel_id');
            $stock_levelService = $this->stockLevelService->forceDelete($stock_level_id);

            return response()->success(
                [
                    'message' => 'stock_level deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
}
