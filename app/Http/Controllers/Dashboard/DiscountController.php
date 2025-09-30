<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends Controller
{


    public function __construct(
        protected DiscountService $discountService
    ) {
       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $discounts = $this->discountService->getAllDiscounts();

        return response()->success([
            'Discounts' => $discounts
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
            $validate = Validator::make(
                $request->only('name','description', 'percntage','start_date','end_date'),
                [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string|max:255',
                    'percntage' => 'required|numeric',
                    'start_date' => 'required',
                    'end_date' => 'required',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                   422
                );
            }


            $discount = $this->discountService->createDiscount($validate->validated());

            return response()->success(
                [
                    'message' => 'Discount Is Created',
                    'data' => $discount
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
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $discount_id = request('discount_id');
            $discount = $this->discountService->getDiscount($discount_id);

            return response()->success(
                [
                    'discount' => $discount
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
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit(Discount $discount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $discount_id = request('discount_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'sometimes|string|max:255',
					'discount_id' => 'required|integer|exists:discounts,id',
                    'description' => 'sometimes|string|max:255',
                    'percntage' => 'sometimes|numeric',
                    'start_date' => 'sometimes',
                    'end_date' => 'sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
              422
                );
            }


            $discount = $this->discountService->updateDiscount( $validate->validated(), $discount_id);

            return response()->success(
                [
                    'message' => 'Discount updated successfully',
                    'data' => $discount
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
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $discount_id = request('discount_id');
            $discount = $this->discountService->delete($discount_id);

            return response()->success(
                [
                    'message' => 'Discount deleted successfully',
                    'data' => $discount
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
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $discount_id = request('discount_id');
            $discount = $this->discountService->forceDelete($discount_id);

            return response()->success(
                [
                    'message' => 'Discount deleted successfully',
                    'data' => $discount
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
