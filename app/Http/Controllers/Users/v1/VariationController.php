<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\Variation;
use App\Http\Controllers\Controller;
use App\Services\VariationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;


class VariationController extends Controller
{


    public function __construct(
        protected VariationService $variationService
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
        $variations = $this->variationService->getAllVariations();

        return response()->success(
             $variations
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
        try {
            $validate = Validator::make($request->all(),
                [
                    'property' => 'required|array',
                    'property.*' => 'max:255',
                    'value' => 'required|array',
                    'value.*' => 'max:255',
                    'hex_code' => 'max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()
                    
                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();

            $variation = $this->variationService->createVariation($validated_data);


            return response()->success(
                [
                    'message'=>'Variation Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_BAD_REQUEST
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Variation  $variation
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $variation_id = request('variation_id');
            $variation = $this->variationService->getVariation($variation_id);

            return response()->success(
                $variation,
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error( $e->getMessage(),
             Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Variation  $variation
     * @return \Illuminate\Http\Response
     */
    public function edit(Variation $variation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variation  $variation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $variation_id = request('variation_id');

            $validate = Validator::make($request->all(),
                [
                    'property' => 'sometimes|array',
                    'property.*' => 'max:255',
                    'value' => 'sometimes|array',
                    'value.*' => 'max:255',
                    'hex_code' => 'max:255||nullable',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();

            $variation = $this->variationService->updateVariation($validated_data, $variation_id);

            return response()->success(
                [
                    'message' => 'Variation updated successfully'
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Variation  $variation
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $variation_id = request('variation_id');
            $variationService = $this->variationService->delete($variation_id);

            return response()->success(
                [
                    'message' => 'Variation deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Variation  $variation
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $variation_id = request('variation_id');
            $variationService = $this->variationService->forceDelete($variation_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully'
                ]
                ,Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                , Response::HTTP_NOT_FOUND
            );
        }
    }
}
