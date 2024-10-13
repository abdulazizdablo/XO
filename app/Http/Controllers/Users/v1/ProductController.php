<?php

namespace App\Http\Controllers\Users\v1;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductFilterRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\Photo;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Discount;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Notify;
use App\Services\FavouriteService;
use App\Services\GroupService;
use App\Services\ProductService;
use App\Services\ProductVariationService;
use App\Services\VariationService;
use App\Services\UserService;
use App\Traits\TranslateFields;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Utils\PaginateCollection;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    use TranslateFields;

    public function __construct(
        protected ProductService $productService,
        protected ProductVariationService $productVariationService,
        protected VariationService $variationService,
        protected FavouriteService $favouriteService,
        protected UserService $userService,
        protected PaginateCollection $paginatecollection,
        protected GroupService $groupService,
    ) {
    }
	
	public function test(Request $request){
		$photos = Group::get();
		foreach($photos as $photo){
			$num = rand(1,85);
			$photo->update([
				'image_path'=>'https://api.xo-textile.sy/public/images/products/('.$num.').webp',
				'image_thumbnail' => 'https://api.xo-textile.sy/public/images/products/('.$num.').webp',
			]);	
		}
		return "done";		
		try {
			$user = auth('sanctum')->user();
			
			if(!$user){
				return response()->json('Unauthorized',403);
			}
            
			$order_id = request('order_id');
			
			DB::beginTransaction();
			$order = Order::where('user_id',$user_id)->findOrFail($order_id);
			if($order->status != 'in_delivery'){
				//throw new Exception('You can not cancel the order now');
				return response()->error(['message' => trans('order_cancel_error',[],$request->header('Content-Language'))],409 );
			}
			else {
				$order->update(['status'=>'canceled']);
				$order_items = $order->order_items()
					->where('status','new')->get();
				$canceled = $order->order_items()->get();
				foreach($canceled as $c){
					$c->update([
						'status' => 'cancelled'
					]);		
				}
				foreach($order_items as $order_item){
					$stock_level = StockLevel::where([['inventory_id',$order_item->to_inventory],
													  ['product_variation_id',$order_item->product_variation_id]])->first();
					if(!$stock_level){
						$stock_level = StockLevel::create([
							'product_variation_id' => $order_item->product_variation_id,
							'inventory_id' => $order_item->to_inventory,
							'name' => Str::random(5),
							'min_stock_level' => 3,
							'max_stock_level' => 1000,
							'target_date' => now(),
							'sold_quantity' => 0,
							'status' => 'slow-movement',
							'current_stock_level' => 0
						]);	
					}

					if ($order->payment_method == 'cod') {
						$stock_level->update([
							'current_stock_level' => $stock_level->current_stock_level + $order_item->quantity,
							'sold_quantity' => $stock_level->sold_quantity - $order_item->quantity
						]);
					} else {
						$stock_level->update([
						'current_stock_level' => $stock_level->current_stock_level + $order_item->quantity,
						'on_hold' => $stock_level->on_hold - $order_item->quantity
					]);
					}
					//$order_item->delete();
				}

				$paid_by_user = $order->paid_by_user;
				$fees = $order->shipping_fee;
				$gift_id = $order->gift_id;
				$covered_by_gift_card = 0;
				if($gift_id){
					$covered_by_gift_card = $order->covered_by_gift_card;
					$coupon = Coupon::where([['user_id', $user_id],['type','gift']])->findOrFail($gift_id);
					$amount_off = $coupon->amount_off;
					$new_amount = $covered_by_gift_card + $amount_off;
					$coupon->update([
					'amount_off' => Crypt::encryptString($new_amount),
				]);
				}

				if($order->payment_method == 'cod'){

					if($order->original_order_id != null){
						$original_order = Order::findOrFail($order->original_order_id);
						$original_items = $original_order->order_items()->get();
						foreach($original_items as $original_item){
							$original_item->update(['status' => null]);	
						}
						$original_order->update(['status'=>'received']);
					}

					DB::commit();
					return "Order was canceled successfully";	
				}
				// return $coupon_password = Crypt::decryptString($coupon->password);
				// return $amount_off = Crypt::decryptString($coupon->amount_off);

				elseif($order->paid == 1){
					Transaction::create([
						'transaction_uuid' => 'refund',
						'order_id'=> $order->id,
						'user_id' => $user_id,
						'amount' => $paid_by_user + $fees,
						'status' => 'pending',
						'payment_method'=> $order->payment_method,
						'operation_type' =>'cancel_order'
					]);
					//return "Order was canceled successfully";	
				}

				if($order->original_order_id != null){
					return $original_order = Order::findOrFail($order->original_order_id);
					$original_order->update(['status'=>'received']);
				}
			DB::commit();
			}
				return response()->success(
					[
						$response
					],
					Response::HTTP_OK
				);
			} catch (\Exception $e) {
				return response()->error(
					[
						"message" => $e->getMessage(),
						"line" => $e->getLine(),
						"file" => $e->getFile()
					],
					Response::HTTP_BAD_REQUEST
				);
			}

	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // $filter = request('filter_data');
            $user_id = 1;
            $search = $request->input('search');
            $filter = $request->only(['price_min', 'price_max', "color", "size", "sort", 'sub_category_id']);

            $products = $this->productService->getAllAvailableProducts($filter, $search, $user_id);


            return response()->success(
                $products,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }
	
	public function productsByGroup(Request $request)
    {
        try {
			$user = auth('sanctum')->user();
			if(!$user){
				return response()->json('Unauthorized',403);
			}
			$user_id = $user->id;
            $page_size = request('pageSize');
            $group_slug = request('group_slug');
			$group = Group::where('slug',$group_slug)->firstOrFail();
			$group_id = $group->id;
            $filter = $request->only(['price_min', 'price_max', "color", "size", "sort", 'sub_category']);
            // $filter['colors'] = $request->input('color', []);
            // $filter['sizes'] = $request->input('size', []);
            $response = $this->productService->getProductsByGroup($group_id, $filter, $page_size, $user_id);

            $products = $this->paginatecollection::paginate(collect($response['products']), $page_size, 20);
            
            //             return response()->json(array_merge([
            //     'success' => true,
            //     'keys' => [
            //         'colors' => $response['colors'],
            //         'sizes' => $response['sizes'],
            //         'sub_categories' => $response['subs'],
            //     ],
            // ], $products->toArray()), 200);

            return response()->json([
                'success' => true,
                'data' =>
                $products
            ], 200);
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function getProductsByCategory(Request $request)
    {
        try {
            $page_size = request('pageSize');
            $category_id = request('category_id');
            $slug = request('slug');
			if(isset($slug)){
				$category = Category::where('slug',$slug)->firstOrFail();
				$category_id = $category->id;
				//return $category_id;
			}
            $filter = $request->only(['price_min', 'price_max', "colors", "size","sizes", "sort", 'sub_category']);
            // $filter['colors'] = $request->input('color', []);
            // $filter['sizes'] = $request->input('size', []);
            $response = $this->productService->getProductsByCategory($category_id, $filter, $page_size);

            $products = $response['products'];

            $products = $this->paginatecollection::paginate(collect($response['products']), $page_size, 10);


            return response()->json(array_merge([
                'success' => true,
                'keys' => [
                    'colors' => $response['colors'],
                    'sizes' => $response['sizes'],
                    'sub_categories' => $response['subs'],
                ],
            ], $products->toArray()), 200);


            // return response()->success(
            //     $response,
            //     Response::HTTP_OK
            // );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function getProductsByCategoryFlutter(Request $request)
    {
        try {
            $page_size = request('pageSize');
            $category_id = request('category_id');
            $filter = $request->only(['price_min', 'price_max', "color", "size", "sort", 'sub_category']);
            // $filter['colors'] = $request->input('color', []);
            // $filter['sizes'] = $request->input('size', []);
            $response = $this->productService->getProductsByCategory($category_id, $filter, $page_size);

            $products = $response['products'];

            $products = $this->paginatecollection::paginate(collect($response['products']), $page_size, 20);


            //             return response()->json(array_merge([
            //     'success' => true,
            //     'keys' => [
            //         'colors' => $response['colors'],
            //         'sizes' => $response['sizes'],
            //         'sub_categories' => $response['subs'],
            //     ],
            // ], $products->toArray()), 200);

            return response()->json([
                'success' => true,
                'keys' => [
                    'colors' => $response['colors'],
                    'sizes' => $response['sizes'],
                    'sub_categories' => $response['subs']
                ],
                'data' =>
                    $products
            ], 200);


            // return response()->success(
            //     $response,
            //     Response::HTTP_OK
            // );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    // public function getGroupProductsBySlug(Request $request)
    // {
    //     try {
    //             // $user_id=auth()->user()?->id;
    //             $user_id = 1;
    //         $slug = request('slug');
    //         $type = request('type');
    //         $filter = $request->only(['price_min', 'price_max', "color", "size", "sort", 'sub_category_id']);

    //         $products = $this->productService->getGroupProductsBySlug($slug, $filter,$user_id);


    //         return response()->success(
    //             $products,
    //             Response::HTTP_OK
    //         );
    //     } catch (Exception $e) {
    //         return response()->error(
    //             $e->getMessage()
    //         , Response::HTTP_NOT_FOUND);
    //     }
    // }

    public function getGroupProductsBySlug(Request $request)
    {
        try {
            $slug = request('slug');
            $type = request('type');
            $filter = $request->only(['price_min', 'price_max', "sort", 'sub_category_id']);

            // Add color and size arrays to the filter
            $filter['colors'] = $request->input('color', []);
            $filter['sizes'] = $request->input('size', []);
            $products = $this->productService->getGroupProductsBySlug($slug, $filter);


            return response()->success(
                $products,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function productReviews(Product $product)
    {




        try {
            if (!$product) {
                throw new Exception('Product does not existed');
            }
            $reviews = $this->productService->getALlProductReviews($product);

            return response()->success(
                $reviews,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND,

            );
        } catch (ModelNotFoundException $th) {
            $model = Str::afterLast($th->getModel(), '\\');
            throw new Exception($model . ' not found', 404);
        }
    }

    public function fuzzySearch(Request $request)
    {
        $category_id = request('category_id');
        $sub_category_id = request('sub_category_id');
        $keyword = request('key');
        $pageSize = request('pageSize');
        $page = request('page');
        $filter = $request->only(['currency', 'price_min', 'price_max', 'sort', 'section']);

        $filter['color'] = request('color', []);
        $filter['size'] = request('size', []);

        $response = $this->productService->fuzzySearch($keyword, $filter, $category_id, $sub_category_id);


        $products = $this->paginatecollection::paginate(collect($response['products']), $pageSize);

        return response()->json(array_merge([
            'success' => true,
            'keys' => [
                'colors' => $response['colors'],
                'sizes' => $response['sizes'],
                'sub_categories' => $response['subs'],
            ],
        ], $products->toArray()), 200);
    }
    // SearchWebsite
    public function SearchWebsite(Request $request)
    {
        $keyword = request('key');


        $filter = $request->only(['currency', 'price_min', 'price_max', 'sort']);

        $filter['color'] = request('color', []);
        $filter['size'] = request('size', []);
        // return $keyword ;

        $products = $this->productService->searchProduct($keyword, $filter);

        return response()->success(
            $products,
            Response::HTTP_OK
        );
    }
    public function getFlashSales()
    {
        $flash_sales_products = $this->productService->getAllFlashSalesProducts();
			
		if(is_string($flash_sales_products) ){
			return response()->error(['message' => 'There is no products'], 404);
		}
		
		return response()->success(
						$flash_sales_products,
						Response::HTTP_OK
		  );
    }

    public function getFavourite()
    {
        try {
            // $user_id = Auth::id();
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->error('Unauthorized', 403);
            }
            $user_id = $user->id;

            // $user_id  = 1;
            $key = request('key');
            $favorite_products = $this->productService->getAllFavouriteProducts($user_id, $key);

            return response()->success(

                $favorite_products,

                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(

                'can not find product',
                Response::HTTP_NOT_FOUND,

            );
        }
    }

    public function removeFavourite()
    {
        try {
            $user = auth('sanctum')->user();
            // $user_id  = $user->id;
            $product_id = request('product_id');
            $data = $this->productService->removeFavourite($user, $product_id);
            return response()->success(
                [
                    'data' => $data,
                    'message' => trans('products.remove_favourite',[],$request->header('Content-Language'))
                ],
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function notifyMe()
    {
        try {
            $user = auth('sanctum')->user();
            $userService = $this->userService->delete( $user->id);

            return response()->success(
                [
                    'message' => 'Notified'
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(ProductFilterRequest $request)
    {
        try {
            $product_slug = $request->product_slug;
            $product_sku = $request->sku;
            $width = $request->width;
            $height = $request->height;
			$enable = $request->enable;



            $product = $this->productService->showProduct($product_slug, $product_sku, $width, $height,$enable);
            // $product = $product->load('discount','product_variations','group','reviews');
            // $product_result = ProductResource::make($product);
            return response()->success(
                $product,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            // return response()->error(
            //     [
            //         $e->getMessage(),
            //         $e->getFile(),
            //         $e->getLine()
            //     ],
            //     Response::HTTP_NOT_FOUND
            // );
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }


    public function mockHttp(Request $request)
    {



        // Define the mock responses for 10 different requests



        // Define the mock responses for 10 different requests
        $mockResponses = [
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 1']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 2']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 3']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 4']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 5']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 6']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 7']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 8']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 9']),
            'https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater' => Http::response(['data' => 'Response 10']),
        ];

        // Use Http::fake to mock the requests
        Http::fake($mockResponses);

        // Make 10 requests and handle their responses
        for ($i = 1; $i <= 100; $i++) {
            $response = Http::get("https://xo-textile.sy/public/api/v1/products/show?product_slug=sweater");

            // Check the response
            if ($response->successful()) {
                echo "Response " . $i . ": " . $response->body() . PHP_EOL;
            } else {
                echo "Error for request " . $i . ": " . $response->status() . PHP_EOL;
            }
        }

        // Remember to call Http::fake() again if you want to reset the mocking for subsequent tests

    }

    public function getProductByItem_no()
    {
        try {
            $item_no = request('item_no');
            $product = $this->productService->getProductByItem_no($item_no);
            //$product_resault = ProductResource::make($product);
            return response()->success(
                $product,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    public function similar_products()
    {
        try {
            $product_id = request('product_id');
            $products = $this->productService->similar_products($product_id);
            return response()->success(
                $products,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }


    public function getUserNofitiedFav(Request $request)
    {


        $user = auth('sanctum')->user()->load(['notified_product_variations', 'favourites_products']);
        if (!$user) {

            return response()->error('Unauthorozied', 401);
        }
        $key = $request->key;
        if ($key == 'favourite') {

            return response()->success(['favourite_products' => $user->favourites_products, 'notified_products' => null], 200);
        } else if ($key == 'notifies') {

            return response()->success(['favourite_products' => null, 'notified_products' => $user->notified_product_variations], 200);
        } else if ($key == 'all' || $key == 'both') {

            $all_products = $user->favourites_products->concat($user->notified_product_variations);
            return response()->success($all_products, 200);

        }
    }




    public function recommendation_products()
    {
        try {
            // $user_id  = 1;
            //  $user_id = request('user_id');

            $user = auth('sanctum')->user();

            if ($user) {
                $user->load(['favourites_products', 'notified_products', 'reviews', 'orders']);
                // Access relationships here
            } else {
                $user = null;
            }
            // if ($user == null) {
            //     $user_id = null;
            // } else {
            //     $user_id  = $user->id;
            // }
            $products = collect($this->productService->recommendation_products($user));

            $products = $this->paginatecollection::paginate($products, 12, 10);
            return response()->success(
                $products,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function addLastViewedProduct($product_id)
    {
        $data = $this->productService->addProductToLastViewed($product_id);
        return response()->success(
            $data,
            Response::HTTP_OK
        );
    }

    public function showLastViewedProducts()
    {
        $products_id = $this->productService->showUserLastViewedProducts();
        return response()->success(
            $products_id,
            Response::HTTP_OK
        );
    }

    public function newIn(ProductFilterRequest $request)
    {
        try {
            $page_size = $request->pageSize;
            $section_id = $request->section_id;
            $category_id = $request->category_id;
            $filter = $request->only(['price_min', 'price_max', "color","colors", "size", "sizes", "sort", 'sub_category']);

            $response = $this->productService->newIn($section_id, $category_id, $filter, $page_size);

            $products = $this->paginatecollection::paginate(collect($response['products']), $page_size, 20);
            return response()->json([
                'success' => true,
                'keys' => [
                    'colors' => $response['colors'],
                    'sizes' => $response['sizes'],
                    'sub_categories' => $response['subs']
                ],
                'data' =>
                    $products
            ], 200);
            // return response()->success(
            //     $products,
            //     Response::HTTP_OK
            // );
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }
    public function top_product(Request $request)
    {
$section = $request->seciton_id;
		
		
						// return response()->error(['message' => trans('products.top_products',[],$request->header('Content-Language'))],400);


		
        $user = auth('sanctum')->user();

		
		
        // $section_id = request('section_id');
        $page_size = request('pageSize');

        $products = $this->productService->top_product($page_size, $user);
	
		$products = collect($products);
			//dd($this->paginatecollection::paginate($products, $page_size,10));
		    $products = $this->paginatecollection::paginate($products, $page_size,10);
        return response()->success(
            $products,
            Response::HTTP_OK
        );
    }


    public function homeSectionProducts(Request $request)
    {
        $home_section = $request->homeSection;
        $pageSize = $request->pageSize;
        $section_id = request('section_id');
        $category_id = request('category_id');
		$group_id = request('group_id');
		
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
            'stock',
            'offer',
            'color',
            'colors',
            'sub_category',
            'size',
            'sizes',
            'sort',
            'group',
            'category'
        ]);

        $sort_data = $request->only([
            'sort_key',
            'sort_value'
        ]);
	
	
        try {
			
            if ($home_section == 'offers') {
                $response = $this->groupService->getAllValidOffers($filter_data, $sort_data, $pageSize, $group_id);
			
                $products = $this->paginatecollection::paginate(collect($response['products']), $pageSize, 10);
                return response()->json([
                    'success' => true,
                    'keys' => [
                        'colors' => $response['colors'],
                        'sizes' => $response['sizes'],
                        'sub_categories' => $response['subs'],
                        'categories' => $response['categories'],
                    ],
                    'data' => $products
                ], 200);
            }
            if ($home_section == 'flash') {
                $response = $this->groupService->getAllValidDiscounts($section_id,$filter_data, $sort_data, $pageSize);
            } else {
                $response = $this->productService->newIn($section_id, $category_id, $filter_data, $pageSize);
            }

            $products = $this->paginatecollection::paginate(collect($response['products']), $pageSize, 10);
            return response()->json([
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



public function unnotify(Request $request){
    // Validate the request data
    $request->validate([
        'product_id' => 'required|exists:products,id',
    ]);

    try {
        // Find the product
        $product = auth('sanctum')->user()->notified_products()->findOrFail($request->product_id);

        // Get product variation IDs
        $product_variations = $product->product_variations->pluck('id');

			//dd(Notify::whereIn('id', $product_variations)->get());
		
        // Delete Notify records
        $notifies = Notify::whereIn('product_variation_id', $product_variations)->delete();
		
	

        // Return a success response
        return response()->json(['product' => ProductResource::make($product),'message' =>  trans('products.product_variations_unnotified',[],$request->header('Content-Language'))], 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return $e;
    }
}

	
	

    public function newlyAdded(Request $request)
    {

        $pageSize = $request->pageSize;
        $products = $this->productService->newlyAddedProducts($pageSize);


        $products = $this->paginatecollection::paginate(collect($products), $pageSize, 20);



        return response()->success($products, 200);
    }
	
	public function adjust(){

 $now = Carbon::now()->startOfDay();
        $day_as_hours = Carbon::now()->addHours(24);
		$stock_levels = StockLevel::whereBetween('updated_at', [$now, $day_as_hours])->with([
               'audits' => function ($query) use ($now, $day_as_hours) {
                  $query->where('auditable_type','App\Models\StockLevel' )->whereBetween('updated_at', [$now, $day_as_hours]);
                   
               }
          ])->get();
        $stock_levels = StockLevel::whereBetween('updated_at', [$now, $day_as_hours])
            ->with([
                'audits' => function ($query) use ($now, $day_as_hours) {
                    $query->whereHas('stockLevel', function ($query) use ($now, $day_as_hours) {
                        $query->whereBetween('updated_at', [$now, $day_as_hours]);
                   });
               }
           ])
           ->get();
        $stock_levels->each(function ($stock_level) {
            $stock_level->audits->each(function ($audit) use ($stock_level) {
                $old_values = json_decode($audit->old_values, true); // it gives this error: [2024-05-02 10:41:01] local.ERROR: json_decode(): Argument #1 ($json) must be of type string, array given {"exception":"[object] (TypeError(code: 0): json_decode(): Argument #1 ($json) must be of type string, array given at /var/www/vhosts/xo-textile.sy/httpdocs/app/Jobs/TestJob.php:60) [stacktrace]
                $new_values = json_decode($audit->new_values, true);

                if (isset ($old_values['current_stock_level'], $new_values['current_stock_level'])) {
                   $difference = $old_values['current_stock_level'] - $new_values['current_stock_level'];
                   $this->updateStockLevelStatus($stock_level, $difference);
               }
           });
      });
    }

    private function updateStockLevelStatus($stock_level, $difference)
    {
        if ($difference > 0 && $difference <= 10) {
            $stock_level->status = 'fast-movement';
        } elseif ($difference > 0 && $difference <= 5) {
            $stock_level->status = 'slow-movement';
        }

        try {
            $stock_level->save();
        } catch (\Exception $e) {
            // Handle exception, e.g., log the error
        }
    }

    // top_product
}
