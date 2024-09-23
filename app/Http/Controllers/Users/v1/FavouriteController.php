<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Services\FavouriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class FavouriteController extends Controller
{
    

    public function __construct(
        protected FavouriteService $favouriteService
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
        $favourites = $this->favouriteService->getAllFavourites();

        return response()->success([
            'favourites' => $favourites
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
            $user=auth('sanctum')->user();
			if(!$user){
			return response()->json('Unautorized',403);
			}
         
            //$user_id = 1;
            $product_id = request('product_id');

            $favourite = $this->favouriteService->createFavourite($user->id, $product_id);

              return response()->success(['message' => trans('favourite.favourite_store',[],$request->header('Content-Language')) ,201]);
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
     * @param  \App\Models\Location  $favourite
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $favourite_id = request('favourite_id');
            $favourite = $this->favouriteService->getFavourite($favourite_id);

            return response()->success([
                'favourite' => $favourite],
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
     * @param  \App\Models\Location  $favourite
     * @return \Illuminate\Http\Response
     */
    public function edit(Favourite $favourite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $favourite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $favourite_id = request('favourite_id');
            $user=auth('sanctum')->user();
            $product_variation_id = request('product_variation');

            $validate = Validator::make($request->all(),
                [
                    'notify' => 'sometimes|boolean',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                          $validate->errors(),
                    
422                );
            }

            $validated_data = $validate->validated();

            $favourite = $this->favouriteService->updateFavourite($validated_data, $favourite_id, $user->id, $product_variation_id);

               			return response()->success(['message' => trans('favourite.favourite_update',[],$request->header('Content-Language')) ,201]);

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
     * @param  \App\Models\Location  $favourite
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $favourite_id = request('favourite_id');
            $favourite = $this->favouriteService->delete($favourite_id);

              			return response()->success(['message' => trans('favourite.favourite_delete',[],$request->header('Content-Language')) ,201]);
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
     * @param  \App\Models\Location  $favourite
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $favourite_id = request('favourite_id');
            $favourite = $this->favouriteService->forceDelete($favourite_id);

            return response()->success(
                [
                    'message' => 'Product deleted successfully',
                    'data' => $favourite,
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
