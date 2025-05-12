<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\OfferService;

use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;

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
    public function store(StoreOfferRequest $request)
    {
        try {
      

            $offerService = $this->offerService->createOffer($request->validated());

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOfferRequest $request)
    {
        try {
          

          
     $validated_data = $request->only([
    'name',
    'type',
    'valid',
    'description',
    'expired_at',
]);

            $offerService = $this->offerService->updateOffer($validated_data,$request->validated('offer_id'));

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
