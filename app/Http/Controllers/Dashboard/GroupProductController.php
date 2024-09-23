<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\GroupOffer;
use App\Http\Controllers\Controller;
use App\Services\GroupOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class GroupOfferController extends Controller
{

    public function __construct(
        protected GroupOfferService $groupOfferService
    ) {
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $group_products = $this->groupOfferService->getAllGroupOffers();

        return response()->success(
            $group_products,
            Response::HTTP_OK
        );
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
                    'name' => 'required|string|array',
                    'name.*' => 'max:255',
                    'address' => 'required|string|array',
                    'address.*' => 'max:255',
                    'city' => 'required|string|array',
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
                    ,
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $group_product = $this->groupOfferService->createGroupOffer($validated_data);

            return response()->success(
                [
                    'message' => 'Group Offer Is Created',
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
     * @param  \App\Models\GroupOffer  $group_product
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $group_product_id = request('location_id');
            $group_product = $this->groupOfferService->getGroupOfferProducts($group_product_id);

            return response()->success(
                $group_product,
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
              $e->getMessage()
            , Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GroupOffer  $group_product
     * @return \Illuminate\Http\Response
     */
    public function edit(GroupOffer $group_product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupOffer  $group_product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $group_product_id = request('location_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'sometimes|string|array',
                    'address' => 'sometimes|string|array',
                    'city' => 'sometimes|string|max:255',
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
                    ,
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $group_product = $this->groupOfferService->updateGroupOffer($validated_data, $group_product_id);

            return response()->success(
                [
                    'message' => 'Group Offer updated successfully'
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
     * @param  \App\Models\GroupOffer  $group_product
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $group_product_id = request('location_id');
            $groupOffer = $this->groupOfferService->delete($group_product_id);

            return response()->success(
                [
                    'message' => 'Group Offer deleted successfully'
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
     * @param  \App\Models\GroupOffer  $group_product
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $group_product_id = request('location_id');
            $groupOffer = $this->groupOfferService->forceDelete($group_product_id);

            return response()->success(
                [
                    'message' => 'Group Offer deleted successfully'
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
