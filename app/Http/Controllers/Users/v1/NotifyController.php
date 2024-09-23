<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\Notify;
use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use App\Models\Product;
use App\Http\Resources\ProductCollection;
use Symfony\Component\HttpFoundation\Response;


class NotifyController extends Controller
{


    public function __construct(
        protected NotifyService $notifyService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifies = $this->notifyService->getAllNotifys();

        return response()->success(
            $notifies
            ,
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
			 $request->only('product_variation_id'),
				[
				  'product_variation_id' => 'required|exists:product_variations,id',
				],

			  );

			  if ($validate->fails()) {
				return response()->error(

				  $validate->errors(),
				  422
				);
			  }
            $product_variation_id = request('product_variation_id');
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;

            $notify = $this->notifyService->createNotify($user_id, $product_variation_id);

            return response()->success(
                $notify,
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                ,
                Response::HTTP_BAD_REQUEST
            );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $notify_id = request('notify_id');
            $notify = $this->notifyService->getNotify($notify_id);

            return response()->success(
                $notify,
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error([
                $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function edit(Notify $notify)
    {
        //
    }

    public function getUserNotifies(Request $request)
    {


        try {
         

           $user=  auth('sanctum')->user()->loadCount('notifies');

            if ($user->notifies_count == 0) {

                return response()->success('You have not notified any products yet', 200);
            }

            $products =  $user->notifies->map(function($item){

                return Product::findOrFail($item->product_id)->load([ 
                'product_variations.size',
                'product_variations.color',
                'product_variations.stock_levels',
                'pricing',
                'reviews',
                'photos:id,product_id,color_id,path,main_photo',
                'group']);
                            });
           
                        if (!$products) {
                            throw new InvalidArgumentException('There Is No Notifys Available');
                        }
                        $products =   ProductCollection::make($products)->sortBy('id')->values()->all();
                      return response()->success($products,200);
                        

            return response()->success($user->notifies, 200);


        } catch (Exception $e) {


            return response()->error(['Something Went Wrong'], 400);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $notify_id = request('notify_id');

            $product_variation_id = request('product_variation_id');
          
            $validate = Validator::make(
                $request->only('notify'),
                [
                    'notify' => 'boolean',
                ],
                [
                    'notify.boolean' => 'this field must be boolean',
                ]
            );

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors()

                    ,
                    422
                );
            }

            $validated_data = $validate->validated();

            $notify = $this->notifyService->updateNotify($validated_data, $notify_id,  $user_id, $product_variation_id);

            return response()->success(
                [
                    'message' => 'Notify updated successfully'
                ],
                Response::HTTP_OK
            );

        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                ,
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $notify_id = request('notify_id');
            $notifyService = $this->notifyService->delete($notify_id);

            return response()->success(
                [
                    'message' => 'Notify deleted successfully'
                ]
                ,
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                ,
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notify  $notify
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $notify_id = request('notify_id');
            $notifyService = $this->notifyService->forceDelete($notify_id);

            return response()->success(
                [
                    'message' => 'Notify deleted successfully'
                ]
                ,
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage()
                ,
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
