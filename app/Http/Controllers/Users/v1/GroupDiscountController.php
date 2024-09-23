<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\GroupDiscount;
use App\Services\GroupDiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class GroupDiscountController extends Controller
{
   

    public function __construct(
        protected GroupDiscountService $groupDiscountProductService
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
        $group_products = $this->groupDiscountProductService->getAllGroupDiscounts();

        return response()->success(
            $group_products
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
                    'name' => 'required|array',
                    'name.*' => 'max:255',
                    'address' => 'required|array',
                    'address.*' => 'max:255',
                    'city' => 'required|array',
                    'city.*' => 'max:255',
                ],
                [
                    'name.required' => 'this field is required',
                    'name.max' => 'this field maximun length is 255',
                    'address.required' => 'this field is required',
                    'address.max' => 'this field maximun length is 255',
                    'city.required' => 'this field is required',
                    'city.max' => 'this field maximun length is 255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();

            $group_product = $this->groupDiscountProductService->createGroupDiscount($validated_data);

          			return response()->success(['message' => trans('group_discount.group_product',[],$request->header('Content-Language')) ,201]);
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
     * @param  \App\Models\GroupProduct  $group_product
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $group_id = request('group_id');
            $group_products = $this->groupDiscountProductService->getGroupDiscountProducts($group_id);

            return response()->success(
                [
                    'products' => $group_products['products'],
                    'offer' => $group_products['offer'],
                ],
            Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                 Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GroupProduct  $group_product
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupProduct  $group_product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $group_product_id = request('group_id');

            $validate = Validator::make($request->all(),
                [
                    'name' => 'sometimes|array',
                    'name.*' => 'max:255',
                    'address' => 'sometimes|array',
                    'address.*' => 'max:255',
                    'city' => 'sometimes|array',
                    'city.*' => 'max:255',
                ],
                [
                    'name.sometimes' => 'this name is sometimes',
                    'address.sometimes' => 'this address is sometimes',
                    'city.sometimes' => 'this phone is sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                          $validate->errors()

                    , Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();

            $group_product = $this->groupDiscountProductService->updateGroupDiscount($validated_data, $group_product_id);

            return response()->success(
                [
                    'message' => 'Group Product updated successfully'
                ],
                Response::HTTP_OK
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
     * @param  \App\Models\GroupProduct  $group_product
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $group_product_id = request('group_id');
            $groupProduct = $this->groupDiscountProductService->delete($group_product_id);

              			return response()->success(['message' => trans('group_offer.group_product_delete',[],$request->header('Content-Language')) ,201]);
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
     * @param  \App\Models\GroupProduct  $group_product
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $group_product_id = request('group_id');
            $groupProduct = $this->groupDiscountProductService->forceDelete($group_product_id);

            return response()->success(
                [
                    'message' => 'Group Product deleted successfully'
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
