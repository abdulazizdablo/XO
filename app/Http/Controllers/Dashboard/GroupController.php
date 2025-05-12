<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Services\GroupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Groups\StoreOfferRequest;
use App\Http\Requests\Groups\StoreDiscountRequest;


class GroupController extends Controller
{
    

    public function __construct(
        protected  GroupService $groupService
    ) {
  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function groups()
    {
        try {
            $type = request('type');
            $groups = $this->groupService->getAllIndexGroups($type);

            return response()->success(
                $groups,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    // 

    public function showgroup()
    {
        try {
            $group_slug = request('group_slug');
            $groups = $this->groupService->ShowGroup($group_slug);

            return response()->success(
                $groups,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function index()
    {
        try {
            $type = request('type');
            $groups = $this->groupService->getAllGroups($type);

            return response()->success(
                $groups,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function discounts()
    {
        try {
            $groups = $this->groupService->getAllDiscountGroups();

            return response()->success(
                $groups,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
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
    public function attachProduct(Request $request)
    {
        try {
            $group_id = request('group_id');
            $products = request('products');
            $group = $this->groupService->attachProduct($group_id, $products);

            return response()->success(
                $group,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    public function detachProduct(Request $request)
    {
        try {
			$delete_many = false;
			
            $group_id = request('group_id');
            $slug = request('slug');
			$delete_many = request('delete_many');
			$products_ids = request('products_ids');
			if($delete_many){
				$group = $this->groupService->detachManyProducts($group_id, $products_ids);	
			}else{
				$group = $this->groupService->detachProduct($group_id, $slug);	
			}
            return response()->success(
                $group,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    public function storeOffer(StoreOfferRequest $request)
    {
        try {

            $group = $this->groupService->storeOffer( $request->validated());

            return response()->success(
                $group,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }


    public function storeDiscount(StoreDiscountRequest $request)
    {
        try {
            $group = $this->groupService->storeDiscount($request->validated());

            return response()->success(
                $group,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $group_id = request('group_id');
            $group = Group::findOrFail($group_id);

            if ($group->type == 'offer') {
                $group->load('products', 'offer');
                $count = $group->products->count();
                $promotion = $group->offers;
            } elseif ($group->type == 'discount') {
                $group->load('productVariations', 'discounts');
                $promotion = $group->discounts;
                $count = $group->productVariations->count();
            }

            return response()->success(
                [
                    "count" => $count,
                    "info" => [
                        'info' => $group->select('id', 'name', 'type', 'valid', 'expired', 'image_thumbnail'),
                        'promotion' => $promotion,
                    ],
                    "items" => '$items'
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

    public function showDashProducts(Request $request)
    {
        try {
            $group_slug = request('group_slug');
            $sort_data = $request->only(['sort_key', 'sort_value']);
            $filter_data = $request->only(['stock_min', 'stock_max', 'added_date']);
            $data = $this->groupService->showDashProducts($group_slug, $sort_data, $filter_data);

            return response()->success(
                $data,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $group_slug = request('group_slug');

            $validate = Validator::make(
                $request->all(),
                [
                    'name_en' => 'string|max:255',
                    'name_ar' => 'string|max:255',
                ],
                [
                    'name.max' => 'this field maximun length is 255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    [
                        'errors' => $validate->errors()
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

     

            $group = $this->groupService->updateGroup( $validate->validated(), $group_slug);

            return response()->success(
                [
                    $group
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
    public function update_valid(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'valid' => 'required|in:0,1',
					'group_slug' => 'required|exists:groups,slug'
                ],

            );

            if ($validate->fails()) {
                return response()->error(
                    [
                        'errors' => $validate->errors()
                    ],
                  422
                );
            }
            
			$group_slug = request('group_slug');

            $validated_data = $validate->validated();

            $group = $this->groupService->updateValidGroup($validated_data, $group_slug);

            return response()->success(
                [
                    $group
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (Exception $th) {
            return response()->error([
                'message'=>$th->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
	public function destroy()
	{   
		try{
			$group_id = request('group_id');
			$group = $this->groupService->delete($group_id);

			return response()->success(
				[
					'message' => 'Group deleted successfully' , $group
				],
				Response::HTTP_OK
			);
        } catch (Exception $th) {
            return response()->error([
                'message'=>$th->getMessage()],
                400
            );
        }
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $group_id = request('group_id');
            $group = $this->groupService->forceDelete($group_id);

            return response()->success(
                [
                    'message' => 'Group deleted successfully'
                ],
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
    public function detach(Request $request)
    {
        try {
            $group_id = request('group_id');
            $product_id = request('product_id');
            $group = $this->groupService->detachDiscount($group_id, $product_id);

            return response()->success(
                $group,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
