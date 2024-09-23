<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
 

    public function __construct(
        protected   OfferService $offerService
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
        $offers = $this->offerService->getAllOffers();

        return response()->success([
            'coupns' => $offers
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
            $validate = Validator::make($request->all(),
                [
                    'name' => 'required|string|max:255',
                    'type' => 'required|string|max:255',
                    'valid' => 'required|boolean',
                    'description' => 'required|string|max:255',
                    'expired_at' => 'required',
                ]
            );

            if ($validate->fails()) {
                return response()->error([
                        'errors' => $validate->errors()
                    ]
                    , Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $offerService = $this->offerService->createOffer($validated_data);

            return response()->success(
                [
                    'message'=>'Offer Is Created',
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
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $offer_id = request('offer_id');
            $offer = $this->offerService->getOffer($offer_id);

            return response()->success([
                'offer' => $offer
            ],
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
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $offer_id = request('offer_id');

            $validate = Validator::make($request->all(),
                [
                    'name' => 'sometimes|string|max:255',
                    'type' => 'sometimes|string|max:255',
                    'valid' => 'sometimes|boolean',
                    'description' => 'sometimes|string|max:255',
                    'expired_at' => 'sometimes',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors()
                    
                    , Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            $offerService = $this->offerService->updateOffer($validated_data, $offer_id);

            return response()->success(
                [
                    'message' => 'Offer Updated Successfully'
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
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $offer_id = request('offer_id');
            $offerService = $this->offerService->delete($offer_id);

            return response()->success(
                [
                    'message' => 'Offer Deleted Successfully'
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
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $offer_id = request('offer_id');
            $offerService = $this->offerService->forceDelete($offer_id);

            return response()->success(
                [
                    'message' => 'Offer Deleted Successfully'
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
