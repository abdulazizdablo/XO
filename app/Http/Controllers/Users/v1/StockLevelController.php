<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\StockLevel;
use App\Services\StockLevelService;

use  App\Http\Requests\StockLevel\StoreStockRequest;
use  App\Http\Requests\StockLevel\UpdateStockRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class StockLevelController extends Controller
{
    

    public function __construct(
        protected StockLevelService $stockLevelService
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
                'products' => $products
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }



    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStockRequest $request)
    {
        $sku_id = request('sku_id');
        $location_id = request('location_id');
        try {
         
            $validated_data = $request->validated();
            $stockLevel_data = $validated_data['stock'];

            $stockLevel = $this->stockLevelService->createStockLevel($stockLevel_data, $sku_id, $location_id);

            return response()->success(
                [
                    'message' => 'Stock Level Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockLevel $stockLevel
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $stockLevel_id = request('stockLevel_id');
            $stockLevel = $this->stockLevelService->getStockLevel($stockLevel_id);

            return response()->success(
                 $stockLevel,
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockLevel $stockLevel
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStockRequest. $request)
    {
        try {
            $location_id = request('location_id');
            $sku_id = request('sku_id');
            $stockLevel_id = request('stockLevel_id');

         

            $validated_data = $request->validated();
            $stockLevel_data = $validated_data['stock'];
          
            var_dump(  $validated_data);
           // $stockLevel = $this->stockLevelService->updateStockLevel($stockLevel_data, $stockLevel_id, $sku_id, $location_id);

            return response()->success(
                [
                    'message' => 'stock_level Updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockLevel $stockLevel
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $stockLevel_id = request('stockLevel_id');
            $stockLevelService = $this->stockLevelService->delete($stockLevel_id);

            return response()->success(
                [
                    'message' => 'Stock Level deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockLevel $stockLevel
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $stockLevel_id = request('stockLevel_id');
            $stockLevelService = $this->stockLevelService->forceDelete($stockLevel_id);

            return response()->success(
                [
                    'message' => 'stock_level deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
