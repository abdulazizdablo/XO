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
use App\Models\ProductVariation;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Color;
use App\Models\Discount;
use App\Models\Category;
use App\Models\AppSetting;
use App\Models\Setting;
use App\Models\Notify;
use App\Services\FavouriteService;
use App\Services\GroupService;
use App\Services\ProductService;
use App\Services\ProductVariationService;
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
use App\Traits\FirebaseNotificationTrait;

class ProductController extends Controller
{
    use TranslateFields;
	use FirebaseNotificationTrait;

    public function __construct(
        protected ProductService $productService,
        protected ProductVariationService $productVariationService,
        protected FavouriteService $favouriteService,
        protected UserService $userService,
        protected PaginateCollection $paginatecollection,
        protected GroupService $groupService,
    ) {}
	

	public function test(Request $request)
	{
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 
		"https://bms.syriatel.sy/API/SendTemplateSMS.aspx?user_name=XO1&password=P@1234567&template_code=XO1_T1&param_list=" . "1234" . "&sender=XO&to=0933096270");
        //"https://bms.syriatel.sy/API/SendSMS.aspx?job_name=XO&user_name=XO&password=P@1234567&msg=Hello_Shafik&sender=xo&to=963933096270");
        // //"https://bms.syriatel.sy/API/CheckJobStatus.aspx?user_name=XO&password=P@1234567&job_id=266946392");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        
		if(curl_exec($ch) === false)
		{
        echo 'Curl error: ' . curl_error($ch);
        }
		
        else
        
		{
			//echo 'Operation completed without any errors';
        }
		
        curl_close($ch);
    
		return "done";
	
		$this->send_notification('cQdYATUHD8s4O4yVxHR4n-:APA91bGtR0K5YR1v7VvMYe99FL7jl6S0qVje9RCIRznf-L0UbD1aoc1GP_d3Ey8y9fMMIXsu3s-LRUnH5TZP46s4tjVgpK-QgZNrEHZHpAbZTFCoX2009ko', 
								 'A new order was created',
								 'A new order was created', 
								 'dashboard_orders,22', 
								 'dashboard');
		
		Employee::find(1)->notifications()->create([
							'employee_id'=>1,
							'type'=> "dashboard_orders,22", // 1 is to redirect to the orders page
							'title'=> ["ar"=>"تم إنشاء طلب شراء جديد",
						"en"=>"A new order was created"],
							'body'=> ["ar"=>"تم إنشاء طلب شراء جديد",
						"en"=>"A new order was created"]
						]);	
		
		return "done";
		try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json('Unauthorized', 403);
            }

            $order_id = request('order_id');

            DB::beginTransaction();
            $order = Order::where('user_id', $user_id)->findOrFail($order_id);
            if ($order->status != 'in_delivery') {
                //throw new Exception('You can not cancel the order now');
                return response()->error(['message' => trans('order_cancel_error', [], $request->header('Content-Language'))], 409);
            } else {
                $order->update(['status' => 'canceled']);
                $order_items = $order->order_items()
                    ->where('status', 'new')->get();
                $canceled = $order->order_items()->get();
                foreach ($canceled as $c) {
                    $c->update([
                        'status' => 'cancelled'
                    ]);
                }
                foreach ($order_items as $order_item) {
                    $stock_level = StockLevel::where([
                        ['inventory_id', $order_item->to_inventory],
                        ['product_variation_id', $order_item->product_variation_id]
                    ])->first();
                    if (!$stock_level) {
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
                if ($gift_id) {
                    $covered_by_gift_card = $order->covered_by_gift_card;
                    $coupon = Coupon::where([['user_id', $user_id], ['type', 'gift']])->findOrFail($gift_id);
                    $amount_off = $coupon->amount_off;
                    $new_amount = $covered_by_gift_card + $amount_off;
                    $coupon->update([
                        'amount_off' => Crypt::encryptString($new_amount),
                    ]);
                }

                if ($order->payment_method == 'cod') {

                    if ($order->original_order_id != null) {
                        $original_order = Order::findOrFail($order->original_order_id);
                        $original_items = $original_order->order_items()->get();
                        foreach ($original_items as $original_item) {
                            $original_item->update(['status' => null]);
                        }
                        $original_order->update(['status' => 'received']);
                    }

                    DB::commit();
                    return "Order was canceled successfully";
                }
                // return $coupon_password = Crypt::decryptString($coupon->password);
                // return $amount_off = Crypt::decryptString($coupon->amount_off);

                elseif ($order->paid == 1) {
                    Transaction::create([
                        'transaction_uuid' => 'refund',
                        'order_id' => $order->id,
                        'user_id' => $user_id,
                        'amount' => $paid_by_user + $fees,
                        'status' => 'pending',
                        'payment_method' => $order->payment_method,
                        'operation_type' => 'cancel_order'
                    ]);
                    //return "Order was canceled successfully";	
                }

                if ($order->original_order_id != null) {
                    return $original_order = Order::findOrFail($order->original_order_id);
                    $original_order->update(['status' => 'received']);
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
	
		public function pointInPolygon(array $point, array $polygon): bool {
		$x = $point[0];
		$y = $point[1];
		$inside = false;
		$numPoints = count($polygon);

		for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
			$xi = $polygon[$i][0];
			$yi = $polygon[$i][1];
			$xj = $polygon[$j][0];
			$yj = $polygon[$j][1];

			$intersect = (($yi > $y) != ($yj > $y)) &&
				($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1) + $xi);
			if ($intersect) {
				$inside = !$inside;
			}
		}
		return $inside;
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) //si
    {
        try {
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
            if (!$user) {
                return response()->json('Unauthorized', 403);
            }
            $user_id = $user->id;
            $page_size = request('pageSize');
            $group_slug = request('group_slug');
            $group = Group::where('slug', $group_slug)->firstOrFail();
            $group_id = $group->id;
            $filter = $request->only(['price_min', 'price_max', "color", "size", "sort", 'sub_category']);
  
            $response = $this->productService->getProductsByGroup($group_id, $filter, $page_size, $user_id);

            $products = $this->paginatecollection::paginate(collect($response['products']), $page_size, 20);

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

    public function getProductsByCategory(Request $request)//si
    {
        try {
            $page_size = request('pageSize');
            $category_id = request('category_id');
            $slug = request('slug');
            if (isset($slug)) {
                $category = Category::where('slug', $slug)->firstOrFail();
                $category_id = $category->id;
            }
            $filter = $request->only(['price_min', 'price_max', "colors", "size", "sizes", "sort", 'sub_category']);
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

    public function getProductsByCategoryFlutter(Request $request)//si
    {
        try {
            $page_size = request('pageSize');
            $category_id = request('category_id');
            $filter = $request->only(['price_min', 'price_max', "color", "size", "sort", 'sub_category']);
            $response = $this->productService->getProductsByCategory($category_id, $filter, $page_size);
            $products = $response['products'];
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

    public function getGroupProductsBySlug(Request $request)//si
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

    public function fuzzySearch(Request $request)//si
    {
        $category_id = request('category_id');
        $sub_category_id = request('sub_category');
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
    public function getFlashSales()//si
    {
        $flash_sales_products = $this->productService->getAllFlashSalesProducts();

        if (is_string($flash_sales_products)) {
            return response()->error(['message' => 'There is no products'], 404);
        }

        return response()->success(
            $flash_sales_products,
            Response::HTTP_OK
        );
    }

    public function getFavourite()//si
    {
        try {
            $user = auth('sanctum')->user();
            
            if (!$user) {
                return response()->success([], Response::HTTP_OK);
            }

            $user_id = $user->id;
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
                    'message' => trans('products.remove_favourite', [], $request->header('Content-Language'))
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
            $userService = $this->userService->delete($user->id);

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
    public function show(ProductFilterRequest $request) //si
    {
        try {
            $product_slug = $request->product_slug;
            $product_sku = $request->sku;
            $width =  $request->width;
            $height = $request->height;
            $enable = $request->enable;
            $product = $this->productService->showProduct($product_slug, $product_sku, $width, $height, $enable);

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


 

    public function getProductByItem_no()//si
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
    public function similar_products()//si
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




    public function recommendation_products()//si
    {
        try {
            $user = auth('sanctum')->user();

            if ($user) {
                $user->load(['favourites_products', 'notified_products', 'reviews', 'orders']);
            } else {
                $user = null;
            }
			
		//	dd($user);

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

    public function showLastViewedProducts()//si
    {
        $products_id = $this->productService->showUserLastViewedProducts();
        return response()->success(
            $products_id,
            Response::HTTP_OK
        );
    }

    public function newIn(ProductFilterRequest $request)//si
    {
        try {
            $page_size = $request->pageSize;
            $section_id = $request->section_id;
            $category_id = $request->category_id;
            $filter = $request->only(['price_min', 'price_max', "color", "colors", "size", "sizes", "sort", 'sub_category']);

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
    public function top_product(Request $request) //si
    {
        $section = $request->seciton_id;
        $user = auth('sanctum')->user();
        // $section_id = request('section_id');
        $page_size = request('pageSize');
		

		
		if ( !isset($page_size) || $page_size == null){
			
		$page_size = 1;

		}
        $products = $this->productService->top_product($page_size, $user);
        $products = collect($products);
        //dd($this->paginatecollection::paginate($products, $page_size,10));
        $products = $this->paginatecollection::paginate($products, $page_size, 10);
        return response()->success(
            $products,
            Response::HTTP_OK
        );
    }


    public function homeSectionProducts(Request $request) //si
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
                $response = $this->groupService->getAllValidDiscounts($section_id, $filter_data, $sort_data, $pageSize);
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

    public function unnotify(Request $request)//si
    { 
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $product = auth('sanctum')->user()->notified_products()->findOrFail($request->product_id);
            $product_variations = $product->product_variations->pluck('id');
            Notify::whereIn('product_variation_id', $product_variations)->delete();
            return response()->json(['product' => ProductResource::make($product), 'message' =>  trans('products.product_variations_unnotified', [], $request->header('Content-Language'))], 200);
        } catch (\Exception $e) {
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

    public function adjust()
    {

        $now = Carbon::now()->startOfDay();
        $day_as_hours = Carbon::now()->addHours(24);
        $stock_levels = StockLevel::whereBetween('updated_at', [$now, $day_as_hours])->with([
            'audits' => function ($query) use ($now, $day_as_hours) {
                $query->where('auditable_type', 'App\Models\StockLevel')->whereBetween('updated_at', [$now, $day_as_hours]);
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

                if (isset($old_values['current_stock_level'], $new_values['current_stock_level'])) {
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
