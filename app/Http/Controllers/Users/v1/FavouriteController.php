<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Services\FavouriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class FavouriteController extends Controller
{
    public function __construct(
        protected FavouriteService $favouriteService
    ) {}

    public function index()
    {
        $favourites = $this->favouriteService->getAllFavourites();

        return response()->success([
            'favourites' => $favourites
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $favourite = $this->favouriteService->createFavourite(
                auth('sanctum')->id(),
                request('product_id')
            );

            DB::commit();

            return response()->success([
                'message' => trans('favourite.favourite_store', [], $request->header('Content-Language'))
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->error($th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function show()
    {
        try {

            $favourite = $this->favouriteService->getFavourite(request('favourite_id'));

            return response()->success([
                'favourite' => $favourite
            ], Response::HTTP_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {

            $validate = Validator::make($request->all(), [
                'notify' => 'sometimes|boolean',
            ]);

            if ($validate->fails()) {
                DB::rollBack();
                return response()->error($validate->errors(), 422);
            }

       

            $favourite = $this->favouriteService->updateFavourite(
               $validate->validated(),
                request('favourite_id'),
                auth('sanctum')->id(),
                request('product_variation')
            );

            DB::commit();

            return response()->success([
                'message' => trans('favourite.favourite_update', [], $request->header('Content-Language'))
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->error($th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy()
    {
        DB::beginTransaction();
        try {
            $favourite_id = request('favourite_id');
            $favourite = $this->favouriteService->delete($favourite_id);

            DB::commit();

            return response()->success([
                'message' => trans('favourite.favourite_delete', [], request()->header('Content-Language'))
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->error($th->getMessage(), Response::HTTP_OK);
        }
    }

    public function forceDelete()
    {
        DB::beginTransaction();
        try {
            $favourite_id = request('favourite_id');
            $favourite = $this->favouriteService->forceDelete($favourite_id);

            DB::commit();

            return response()->success([
                'message' => 'Product deleted successfully',
                'data' => $favourite,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->error($th->getMessage(), Response::HTTP_OK);
        }
    }
}
