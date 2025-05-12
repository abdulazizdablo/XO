<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class StockMovementController extends Controller
{

    public function __construct(
        protected StockMovementService $stockMovementService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter_data = $request->only(["shipping_min", "shipping_max", "create_min", "create_max", "received_min", "received_max", "status", "search"]);
        // sort key : created_at, delivery_date, shipped_date, received_date
        // $sort_data = $request->only(["sort_key", "sort_value"]);
        $stockMovements = $this->stockMovementService->getAllStockMovements($filter_data);

        return response()->success(
            $stockMovements,
            Response::HTTP_OK
        );
    }

    public function getCounts()
    {
        $counts = $this->stockMovementService->getCounts();

        return response()->success(
            $counts,
            Response::HTTP_OK
        );
    }

    public function send(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'product_variation_id' => 'required|integer|exists:product_variations,id',
                    'from_inventory_id' => 'required|integer|exists:inventory,id',
                    'to_inventory_id' => 'required|integer|exists:inventory,id',
                    'num_of_packages' => 'required|integer',
                    'delivery_date' => 'required|date',
                    'expected' => 'required|integer'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $stock_movement_data = $validate->validated();
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
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

            $validate = Validator::make(
                $request->all(),
                [
                    'product_variation_id' => 'required|integer',
                    'from_inventory_id' => 'required|integer',
                    'to_inventory_id' => 'required|integer',
                    'num_of_packages' => 'required|integer',
                    'delivery_date' => 'required|date',
                    'expected' => 'required|integer'
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $stock_movement_data = $validate->validated();

            $stock_movement = $this->stockMovementService->createStockMovement($stock_movement_data);

            return response()->success(
                $stock_movement,
                Response::HTTP_CREATED
            );
        } catch (\Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
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
    public function upload(Request $request)
    {
        try {
            $stock_movement_id = request('stock_movement_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'file' => 'required|file|mimes:pdf|between:1,3072',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $stock_movement_data = $validate->validated();

            $stock_movement = $this->stockMovementService->uploadAttachment($stock_movement_data, $stock_movement_id);

            return response()->success(
                $stock_movement,
                Response::HTTP_CREATED
            );
        } catch (\Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     *
     * Store a newly bulk created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkStore(Request $request)
    {
        try {
             $employee_id = auth()->guard('api-employees')->user()->id;
             $from_inventory_id = $employee_id;
          //  $from_inventory_id = 1;

            $validate = Validator::make(
                $request->all(),
                [
                    'data' => 'required|array',
                    'data.*.product_variation_id' => 'required|integer',
                    'data.*.num_of_packages' => 'required|integer',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();
            $stock_movement_data = $validated_data['data'];

            DB::beginTransaction();

            $stock_movements = $this->stockMovementService->bulkCreateStockMovement($stock_movement_data, $from_inventory_id);

            DB::commit();

            return response()->success(
                $stock_movements,
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
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
            $stock_movement_id = request('stock_movement_id');
            $stockMovement = $this->stockMovementService->getStockMovement($stock_movement_id);

            return response()->success(
                [
                    'stock_movement' => $stockMovement
                ],
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showItems()
    {
        try {
            $stock_movement_id = request('stock_movement_id');
            $stockMovement = $this->stockMovementService->getShowItems($stock_movement_id);

            return response()->success(
                $stockMovement,
                Response::HTTP_OK
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

            $validate = Validator::make(
                $request->all(),
                [
                    'stock_movement.type' => 'sometimes|string|max:255',
                    'stock_movement.quantity' => 'sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
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
                $th->getMessage(),
                Response::HTTP_OK
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
