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
            $group_id = request('group_id');
             $slug = request('slug');
			
            $group = $this->groupService->detachProduct($group_id, $slug);

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
    public function storeOffer(Request $request)
    {
        try {
            // $products = request('products');
            // $discounts = request('discounts');
            $validate = Validator::make(
                $request->all(),
                [
                    'group_name_ar' => 'required|string|max:255',
                    'group_name_en' => 'required|string|max:255',
                    'start_date' => 'required_if:group_type,discount|max:255',
                    'promotion_name' => 'required|string|max:255',
                    'end_date' => 'required_if:group_type,discount|max:255',
                    'percentage' => 'required_if:group_type,discount|integer|lte:90',
                    'promotion_type' => 'required_if:group_type,offer|in:BOGO,BOGH,BTGO',
                    'number_of_items' => 'required_if:group_type,offer|max:255',
                    'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
        422
                );
            }

         

            $group = $this->groupService->storeOffer( $validate->validated());

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


    public function storeDiscount(Request $request)
    {
        try {
            // $products = request('products');
            // $discounts = request('discounts');
            $validate = Validator::make(
                $request->all(),
                [
                    'group_name_ar' => 'required|string|max:255',
                    'group_name_en' => 'required|string|max:255',
                    'promotion_name' => 'required|string|max:255',
                    'start_date' => 'required_if:group_type,discount|max:255',
                    'end_date' => 'required_if:group_type,discount|max:255',
                    'percentage' => 'required_if:group_type,discount|integer|lte:90',
                    'promotion_type' => 'required_if:group_type,discount|in:flash_sales',
                    'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                 422
                );
            }

            $group = $this->groupService->storeDiscount($validate->validated());

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
                        'info' => $group->select('id', 'name', 'type', 'valid', 'tag', 'image_thumbnail'),
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
            $group_slug = request('group_slug');

            $validate = Validator::make(
                $request->all(),
                [
                    'valid' => ''
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
            return response()->error(
                $th->getMessage(),
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
        try {
            $group_slug = request('group_slug');
            $group = $this->groupService->delete($group_slug);

            return response()->success(
                [
                    'message' => 'Group deleted successfully' , $group
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
