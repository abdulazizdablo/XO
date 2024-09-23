<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Pricing;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class PricingController extends Controller
{


    public function __construct(
        protected  PricingService $pricingService
    ) {
     
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pricings = $this->pricingService->getAllPricings();

        return response()->success([
            'Pricings' => $pricings
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
            $product_id = request('product_id');
          $validate = Validator::make(
    $request->only('name', 'value', 'currency', 'locale'),
    [
        'name' => 'required|string|max:255',
        'value' => 'required|string|max:255',
        'currency' => 'required|string|max:255|in:sp,ae',
        'locale' => 'required|string|max:255',
    ]
);


            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $pricing = $this->pricingService->createPricing($validated_data, $product_id);

            return response()->success(
                [
                    'message' => 'Pricing Is Created',
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
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $pricing_id = request('pricing_id');
            $pricing = $this->pricingService->getPricing($pricing_id);

            return response()->success(
                [
                    'pricing' => $pricing
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
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function edit(Pricing $pricing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $pricing_id = request('pricing_id');
            $product_id = request('product_id');

         $validate = Validator::make(
    $request->only('name', 'value', 'currency', 'locale'),
    [
        'name' => 'required|string|max:255',
        'value' => 'required|string|max:255',
        'currency' => 'required|string|max:255|in:sp,ae',
        'locale' => 'required|string|max:255',
    ]
);

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $pricing = $this->pricingService->updatePricing($validated_data, $pricing_id, $product_id);

            return response()->success(
                [
                    'message' => 'Pricing updated successfully'
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
     * Bulk Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        try {

            $validate = Validator::make(
                $request->only( 'products_ids', 'price'),
                [
                    'products_ids' => 'required|array',
                    'price' => 'required|numeric',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $products_data = $validate->validated();

            DB::beginTransaction();

            $result = $this->pricingService->bulkUpdatePricing($products_data);

            DB::commit();

            return response()->success(
                $result,
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $pricing_id = request('pricing_id');
            $pricingService = $this->pricingService->delete($pricing_id);

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
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $pricing_id = request('pricing_id');
            $pricingService = $this->pricingService->forceDelete($pricing_id);

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
}
