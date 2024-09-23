<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class StockMovementController extends Controller
{


    public function __construct(
        protected  StockMovementService $stockMovementService
        )
    {
    
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stockMovements = $this->stockMovementService->getAllStockMovements();

        return response()->success([
            'Stock Movements' => $stockMovements
        ], Response::HTTP_OK);
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
        try {
            $sku_id = request('sku_id');
            $location_id = request('location_id');

            $validate = Validator::make($request->all(),
                [
                    'stock_movement.type' => 'required|max:255',
                    'stock_movement.quantity' => 'required',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()

                    , Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();
            $stockMovement_data = $validated_data['stock_movement'];

            $stockMovement = $this->stockMovementService->createStockMovement($stockMovement_data, $sku_id, $location_id);

            return response()->success(
                [
                    'message'=>'Stock movement Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockMovement  $stockMovement
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $stockMovement_id = request('stockMovement_id');
            $stockMovement = $this->stockMovementService->getStockMovement($stockMovement_id);

            return response()->success([
                'stock_movement' => $stockMovement],
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                  $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockMovement  $stockMovement
     * @return \Illuminate\Http\Response
     */
    public function edit(StockMovement $stockMovement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockMovement  $stockMovement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $sku_id = request('sku_id');
            $location_id = request('location_id');
            $stockMovement_id = request('stockMovement_id');

            $validate = Validator::make($request->all(),
                [
                    'stock_movement.type' => 'sometimes|string|max:255',
                    'stock_movement.quantity' => 'sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                          $validate->errors()
                    
                    , Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();
            $stockMovement_data = $validated_data['stock_movement'];

            $stockMovement = $this->stockMovementService->updateStockMovement($stockMovement_data, $stockMovement_id, $sku_id, $location_id);

            return response()->success(
                [
                    'message' => 'Stock movement Update Successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockMovement  $stockMovement
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $stockMovement_id = request('stockMovement_id');
            $stockMovementService = $this->stockMovementService->delete($stockMovement_id);

            return response()->success(
                [
                    'message' => 'Stock Movement deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockMovement  $stockMovement
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $stockMovement_id = request('stockMovement_id');
            $stockMovementService = $this->stockMovementService->forceDelete($stockMovement_id);

            return response()->success(
                [
                    'message' => 'Stock Movement deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_OK
            );
        }
    }
}
