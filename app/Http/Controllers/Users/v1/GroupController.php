<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductCollectionCo;

use App\Services\GroupService;
use App\Utils\PaginateCollection as UtilsPaginateCollection;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Utils\PaginateCollection;
use App\Http\Resources\ProductVariationCollection;

class GroupController extends Controller
{

    public function __construct(
        protected   GroupService $groupService,
        protected UtilsPaginateCollection $pagainte
    ) {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $type = request('type');
            $groups = $this->groupService->getAllValidGroups($type);

            return response()->success(
                $groups,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
	
	public function offersNames(){
		$offers = Group::where('type','offer')->valid()->select('id','name','slug','image_path')->get();
	return response()->success(
                $offers,
                Response::HTTP_OK
            );	
	}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function groupsProducts()
    {
        try {
            // $group_id = request('group_id');
            // $type = request('type');
            $group_products = $this->groupService->getGroupsProducts();

            return response()->success(
                $group_products,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function show()
    {
        try {
            $group_id = request('group_id');
            $type = request('type');
            $group_products = $this->groupService->getGroupProducts($group_id, $type);

            return response()->success(
                ProductCollection::make($group_products),
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function discounts(Request $request)
    {
        $pageSize = $request->pageSize;

        $filter_data = $request->only([
            'inventory',
            'status',
            'pricing_min',
            'pricing_max',
            'quantity',
            'date_min',
            'date_max',
            'delivery_min',
            'delivery_max',
            'search',
            'stock',
            'offer',
            'color',
            'sub_category',
            'size',
            'sort',
            'group',
            'category'
        ]);

        $sort_data = $request->only([
            'sort_key',
            'sort_value'
        ]);
        try {
            $response = $this->groupService->getAllValidDiscounts($filter_data, $sort_data, $pageSize);
            $products = $this->pagainte::paginate(collect($response['products']),  $pageSize , 10);
               return response()->json(array_merge([
    'success' => true,
    'keys' => [
        'colors' => $response['colors'],
        'sizes' => $response['sizes'],
        'sub_categories' => $response['subs'],
    ],
], $products->toArray()), 200);
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    
        public function discountsFlutter(Request $request)
    {

        $pageSize = $request->pageSize;

        $filter_data = $request->only([
            'inventory',
            'status',
            'pricing_min',
            'pricing_max',
            'quantity',
            'date_min',
            'date_max',
            'delivery_min',
            'delivery_max',
            'search',
            'stock',
            'offer',
            'color',
            'sub_category',
            'size',
            'sort',
            'group',
            'category'
        ]);

        $sort_data = $request->only([
            'sort_key',
            'sort_value'
        ]);
        try {
            $response = $this->groupService->getAllValidDiscounts($filter_data, $sort_data, $pageSize);
            $products = $this->pagainte::paginate(collect($response['products']),  $pageSize , 10);
                           return   response()->json([
                'success' => true,
                'keys' => [
                    'colors' => $response['colors'],
                    'sizes' => $response['sizes'],
                    'sub_categories' => $response['subs'],
                    'categories' => $response['categories'],
                ],
                'data' => $products
            ], 200);
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    
    public function latestOffers(){
        try {
        $response = $this->groupService->getLatestOffers();
        return response()->success(
            $response,
            Response::HTTP_OK
        );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
	
	
	public function offerAppliedProduct(Request $request){
	
	$order_item_id = $request->order_item_id;
	$offer_item_ids = $request->offer_item_ids;
		$offer_products = $this->groupService->getOfferProducts($order_item_id ,$offer_item_ids)->values();
		
		
		return ProductCollectionCo::make(	$offer_products);
	
	
	}
	

    public function offers(Request $request)
    {

        $pageSize = $request->pageSize;
        $filter_data = $request->only([
            'inventory',
            'status',
            'price_min',
            'price_max',
            'quantity',
            'date_min',
            'date_max',
            'delivery_min',
            'delivery_max',
            'search',
            'stock_min',
            'stock_max',
            'offer',
            'color',
            'sub_category',
            'sub_category_id',
            'size',
            'sort',
            'group',
			'section',
            'category',
            'category_id'
        ]);

        $sort_data = $request->only([
            'sort_key',
            'sort_value'
        ]);
        try {
            $response = $this->groupService->getAllValidOffers($filter_data, $sort_data, $pageSize);


            $products = $this->pagainte::paginate(collect($response['products']),  $pageSize , 10);
            return   response()->json([
                'success' => true,
                'keys' => [
                    'colors' => $response['colors'],
                    'sizes' => $response['sizes'],
                    'sub_categories' => $response['subs']
                ],
                'data' => $products
            ], 200);
            //  this->pagainte::paginate($response,20,10);
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    public function types()
    {
        try {
            $type = request('type');
            $types = $this->groupService->getTypes($type);

            return response()->success(
                $types,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    // types

}
